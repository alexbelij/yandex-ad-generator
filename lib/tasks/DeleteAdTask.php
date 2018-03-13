<?php
/**
 * Project Golden Casino.
 */

namespace app\lib\tasks;

use app\helpers\JsonHelper;
use app\lib\api\yandex\direct\exceptions\ConnectionException;
use app\lib\api\yandex\direct\query\ResultItem;
use app\lib\api\yandex\direct\resources\AdGroupResource;
use app\lib\api\yandex\direct\resources\AdResource;
use app\lib\services\AdGroupService;
use app\lib\services\AdService;
use app\models\Ad;
use app\models\AdYandexCampaign;
use app\models\YandexUpdateLog;
use app\helpers\ArrayHelper;

/**
 * Снятие объявлений в яндексе и их удаление
 *
 * Class DeleteAdTask
 * @package app\lib\tasks
 */
class DeleteAdTask extends YandexBaseTask
{
    const TASK_NAME = 'deleteAd';

    /**
     * @var int
     */
    protected $count = 0;

    /**
     * @var AdService
     */
    protected $adService;

    /**
     * @var AdGroupService
     */
    protected $adGroupService;

    /**
     * @var array
     */
    protected $restoreIds = [];

    /**
     * @inheritDoc
     */
    protected function init()
    {
        parent::init();
        $this->adService = new AdService(new AdResource($this->connection));
        $this->adGroupService = new AdGroupService(new AdGroupResource($this->connection));
    }

    /**
     * @inheritDoc
     */
    public function execute($params = [])
    {
        $this->log('Запущена задача удаления объявлений');
        while (true) {
            $ads = $this->getAds();
            $this->log("Получено " . count($ads) . " объявлений для удаления");
            if (empty($ads)) {
                break;
            }

            try {
                $this->process($ads);
            } catch (\Exception $e) {
                $adIds = ArrayHelper::getColumn($ads, 'id');
                Ad::updateAll(['is_deleted' => 1], ['id' => $adIds]);
                throw $e;
            }
        }

        if (!empty($this->restoreIds)) {
            Ad::updateAll(['is_deleted' => 1], ['id' => $this->restoreIds]);
        }

        $this->log("Снято объявлений: $this->count");
    }

    /**
     * @param Ad[] $ads
     * @throws ConnectionException
     */
    protected function process($ads)
    {
        $deleteSql = 'ad_id IN (' . implode(',', ArrayHelper::getColumn($ads, 'id')) . ') AND (yandex_ad_id IS NULL OR is_published = 0)';
        AdYandexCampaign::deleteAll($deleteSql);

        /** @var AdYandexCampaign[] $yandexAds */
        $yandexAds = AdYandexCampaign::find()
            ->with('yandexCampaign')
            ->andWhere(['ad_id' => ArrayHelper::getColumn($ads, 'id')])
            ->indexBy('yandex_ad_id')
            ->all();

        if (!empty($yandexAds)) {

            $result = [];
            foreach (ArrayHelper::index($yandexAds, null, 'account_id') as $accountId => $accountYandexAds) {
                $yandexAdIds = ArrayHelper::getColumn($accountYandexAds, 'yandex_ad_id');

                if (!empty($yandexAdIds)) {
                    try {
                        $result = array_merge($result, $this->deleteAds($accountId, $yandexAdIds));
                    } catch (ConnectionException $e) {
                        if ($e->getCode() == ConnectionException::TOKEN_NOT_FOUND) {
                            AdYandexCampaign::deleteAll(['yandex_ad_id' => $yandexAdIds]);
                        } else {
                            throw $e;
                        }
                    }
                }
            }

            $successIds = [];
            $errorIds = [];
            $adGroupIdsToDelete = [];
            /** @var ResultItem $item */
            foreach ($result as $item) {
                if (!isset($yandexAds[$item->getId()])) {
                    continue;
                }
                $yandexAd = $yandexAds[$item->getId()];

                if ($item->hasError()) {
                    $errorMsg = "Ошибка при снятии объявления: {$yandexAd->id}, {$yandexAd->ad->title}, " .
                        "сообщение: {$item->firstError()->errorInfo()}";
                    $this->logOperation($yandexAd, 'removeAd', YandexUpdateLog::STATUS_ERROR, $errorMsg);
                    $errorIds[] = $item->getId();
                } else {
                    $successIds[] = $item->getId();
                    $adGroupIdsToDelete[] = $yandexAd->yandex_adgroup_id;
                    $yandexAd->is_published = 0;
                    $yandexAd->save();
                    $msg = "Объявление {$yandexAd->id}, {$yandexAd->ad->title} успешно снято";
                    $this->logOperation($yandexAd, 'removeAd', YandexUpdateLog::STATUS_SUCCESS, $msg);
                }
            }

            $this->log("Снято " . count($successIds) . " объявлений");

            if (!empty($adGroupIdsToDelete)) {
                $this->adGroupService->delete($adGroupIdsToDelete);
            }

            if (!empty($successIds)) {
                AdYandexCampaign::deleteAll(['yandex_ad_id' => $successIds]);
            }
        }

        foreach ($ads as $ad) {
            $yandexAdCount = AdYandexCampaign::find()->andWhere(['ad_id' => $ad->primaryKey])->count();
            $this->logger->log('Объявление: ' . JsonHelper::encodeModelPretty($ad));
            if (!$yandexAdCount) {
                $this->count++;
                $this->logOperation($ad, 'removeAd', YandexUpdateLog::STATUS_SUCCESS, "Удаление объявления: $ad->id, $ad->title");
                $ad->delete();
            } else {
                $this->restoreIds[] = $ad->primaryKey;
                $this->logOperation($ad, 'removeAd', YandexUpdateLog::STATUS_ERROR, "Имеются размещенные объявления");
            }
        }
    }

    /**
     * @param $accountId
     * @param int[] $yandexAdIds
     * @return ResultItem[]
     */
    protected function deleteAds($accountId, array $yandexAdIds)
    {
        $identity = $this->setAccountToken($accountId);
        $this->logger->log('Установленный токен: ' . $identity->getToken());
        $this->logger->log('Используем аккаунт id: ' . $accountId);
        $this->logger->log('Удаляем объявления с yandex_ad_id: ' . json_encode($yandexAdIds));

        $deleteResult = $this->adService->removeAds($yandexAdIds);

        $resultIds = [];

        foreach ($deleteResult->getResult() as $item) {
            if ($item->getId()) {
                $resultIds[] = $item->getId();
            }
        }

        if (count(array_filter($resultIds)) == 0) {
            AdYandexCampaign::deleteAll(['yandex_ad_id' => $yandexAdIds]);
        }

        $this->logger->log(
            'Ответ директа: ' . json_encode($deleteResult->getResult(), JSON_UNESCAPED_UNICODE)
        );

        return $deleteResult->getResult();
    }

    /**
     * Метод возвращает объявления, которые были помечены для удаления
     *
     * @return Ad[]|null
     */
    protected function getAds()
    {
        $transaction = \Yii::$app->db->beginTransaction();
        $sql = 'SELECT {{%ad}}.* FROM {{%ad}}
                INNER JOIN product p on p.id = {{%ad}}.product_id
                WHERE is_deleted = 1 AND p.shop_id = :shopId 
                ORDER BY {{%ad}}.id asc LIMIT 500 FOR UPDATE';

        $this->logger->log(
            'Запрос: ' . \Yii::$app->db->createCommand($sql, [':shopId' => $this->task->shop_id]
            )->getRawSql());

        /** @var Ad[] $ad */
        $ads = Ad::findBySql($sql, [':shopId' => $this->task->shop_id])
            ->all();

        if (empty($ads)) {
            $transaction->rollBack();
            return [];
        }

        Ad::updateAll(['is_deleted' => 2], ['id' => ArrayHelper::getColumn($ads, 'id')]);
        $transaction->commit();

        return $ads;
    }

    /**
     * @param string $message
     */
    protected function log($message)
    {
        $this->getLogger()->log($message);
    }

    /**
     * @inheritDoc
     */
    protected function getLogFileName()
    {
        return 'delete_ad_' . $this->task->id;
    }
}
