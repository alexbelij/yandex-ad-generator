<?php

namespace app\lib\tasks;

use app\lib\api\auth\ApiAccountIdentity;
use app\lib\api\yandex\direct\exceptions\YandexException;
use app\lib\api\yandex\direct\query\ResultItem;
use app\lib\api\yandex\direct\resources\AdResource;
use app\lib\api\yandex\direct\resources\SitelinksResource;
use app\lib\services\AdService;
use app\lib\services\SitelinksService;
use app\models\Account;
use app\models\AdYandexCampaign;
use app\models\Product;
use app\models\Shop;
use app\models\YandexUpdateLog;
use app\helpers\ArrayHelper;

/**
 * Обновление быстрых ссылок
 *
 * Class SitelinksUpdateTask
 * @package app\lib\tasks
 */
class SitelinksUpdateTask extends YandexBaseTask
{
    const TASK_NAME = 'sitelinksUpdate';
    
    /**
     * @var SitelinksService
     */
    protected $sitelinksService;

    /**
     * @var AdService
     */
    protected $adService;

    /**
     * @inheritdoc
     */
    protected function init()
    {
        parent::init();
        $siteLinksResource = new SitelinksResource($this->connection);

        $this->sitelinksService = new SitelinksService($siteLinksResource);
        $this->adService = new AdService(new AdResource($this->connection), $this->sitelinksService);
    }

    /**
     * @inheritDoc
     */
    public function execute($params = [])
    {
        $query = AdYandexCampaign::find()
            ->innerJoinWith('ad.product')
            ->andWhere(['is_published' => 1])
            ->andWhere([
                '{{%product}}.shop_id' => $this->shop->id,
                '{{%product}}.is_available' => 1,
            ]);

        $this->getLogger()->log('Начинаем синхронизацию sitelinks');
        $this->getLogger()->log('Запрос: ' . $query->createCommand()->getRawSql());

        /** @var AdYandexCampaign[] $ads */
        foreach ($query->batch(500) as $ads) {

            $this->getLogger()->log('Получено ' . count($ads) . ' объявлений');

            $result = [];
            foreach (ArrayHelper::groupBy($ads, 'account_id') as $accountId => $accountAds) {
                $account = Account::findOne($accountId);
                if (!$account) {
                    $this->getLogger()->log("Аккаунт $accountId не найден");
                    continue;
                }
                $this->getLogger()->log('Используем аккаунт: ' . $account->title);

                $this->connection->setAuthIdentity(new ApiAccountIdentity($account));
                $newSiteLinkId = $this->createSiteLink($this->shop, $account);
                $productIds = ArrayHelper::getColumn($accountAds, 'ad.product_id');
                if ($productIds) {
                    Product::updateAll(['yandex_sitelink_id' => $newSiteLinkId], ['id' => $productIds]);
                }

                $yandexAdIds = ArrayHelper::getColumn($accountAds, 'yandex_ad_id');
                $ads = ArrayHelper::index($accountAds, 'yandex_ad_id');
                $this->getLogger()->log('Обновление быстрых ссылок для объявлений: ' . json_encode($yandexAdIds, JSON_PRETTY_PRINT));
                $updateResult = $this->adService->updateSitelinksId($yandexAdIds, $newSiteLinkId);

                if (!$updateResult->isSuccess() && count($updateResult->getResult()) == 1) {
                    throw new YandexException($updateResult->firstError()->errorInfo());
                }

                $result = array_merge($result, $updateResult->getResult());
            }

            /** @var ResultItem $resultItem */
            foreach ($result as $resultItem) {
                $ad = !empty($ads[$resultItem->getId()]) ? $ads[$resultItem->getId()] : null;
                if (!$ad) {
                    $this->getLogger()->log('Не удалось найти объявление с id - ' , $resultItem->getId());
                    continue;
                }
                if ($resultItem->isOk()) {
                    $ad->updateUploadDate();
                    $this->logOperation($ad, YandexUpdateLog::OPERATION_SITELINKS_UPDATE, YandexUpdateLog::STATUS_SUCCESS);
                } else {
                    $error = $resultItem->firstError();
                    $errorMsg = $error ? $error->errorInfo() : '';
                    $this->logOperation(
                        $ad, YandexUpdateLog::OPERATION_SITELINKS_UPDATE, YandexUpdateLog::STATUS_ERROR, $errorMsg
                    );
                }
            }
        }
    }

    /**
     * @param Shop $shop
     * @param Account $account
     * @return mixed
     */
    protected function createSiteLink(Shop $shop, Account $account)
    {
        static $cache = [];
        $key = "{$shop->id}:{$account->id}";
        if (!array_key_exists($key, $cache)) {
            $yandexSiteLink = $this->sitelinksService->createForShop($this->shop, $account);
            $cache[$key] = $yandexSiteLink->yandex_id;
        }

        return $cache[$key];
    }
}
