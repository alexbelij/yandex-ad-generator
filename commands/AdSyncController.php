<?php

namespace app\commands;

use app\components\FileLogger;
use app\components\LoggerInterface;
use app\helpers\AccountHelper;
use app\lib\api\auth\ApiAccountIdentity;
use app\lib\api\yandex\direct\Connection;
use app\lib\api\yandex\direct\query\ad\AdSelectionCriteria;
use app\lib\api\yandex\direct\query\AdQuery;
use app\lib\api\yandex\direct\resources\AdResource;
use app\lib\Collection;
use app\lib\Logger;
use app\lib\services\AdStatusUpdater;
use app\lib\services\KeywordsSyncService;
use app\models\Account;
use app\models\AdYandexCampaign;
use app\models\BrandAccount;
use app\models\Shop;
use app\models\YandexCampaign;
use yii\console\Controller;
use yii\helpers\ArrayHelper;

/**
 * Контроллер синхронизации объявлений рекламных кампаний с генератором
 *
 * Class AdSyncController
 * @package app\commands
 */
class AdSyncController extends Controller
{
    /**
     * @var int
     */
    public $shopId;

    /**
     * @var string
     */
    public $campaignIds;

    /**
     * @var AdResource
     */
    protected $adResource;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Количество восстановленных объявлений
     *
     * @var int
     */
    protected $totalRestoreCount = 0;

    /**
     * Количество снятых объявлений
     *
     * @var int
     */
    protected $totalSuspendCount = 0;

    /**
     * Количество заархивированных объявлений
     *
     * @var int
     */
    protected $totalArchivedCount = 0;

    /**
     * Количество удаленных объявлений
     *
     * @var int
     */
    protected $totalDeletedCount = 0;

    /**
     * @inheritdoc
     */
    public function options($actionId)
    {
        return ['shopId', 'campaignIds'];
    }

    /**
     * @param int $shopId
     * @param string $campaignIds
     */
    public function actionIndex($shopId, $campaignIds = null)
    {
        /** @var Shop $shop */
        $shop = Shop::findOne($shopId);

        $this->logger = new FileLogger('sync_ad_shop_' . $shopId . '_' . date('Y.m.d_H:i'));

        $this->logger->log('Начало синхронизации');

        $existsCampaignIds = YandexCampaign::find()
            ->select('yandex_id')
            ->andWhere(['shop_id' => $shopId])
            ->column();

        $connection = new Connection(new ApiAccountIdentity($shop->account));
        $this->adResource = new AdResource($connection);
        $campaignIds = array_values(array_filter(array_map('intval', explode(',', $campaignIds))));

        $yaCamaignIdsCollection = new Collection(
            array_map('intval', array_merge($existsCampaignIds, $campaignIds))
        );

        $this->logger->log(
            'Синхронизация запущена для кампаний: ' .
            implode(', ', array_map('intval', array_merge($existsCampaignIds, $campaignIds)))
        );

        /** @var YandexCampaign[] $yandexCampaigns */
        foreach ($yaCamaignIdsCollection->batch(10) as $yaCampaignIds) {
            list($accounts, $accountsCampaigns) = AccountHelper::groupCampaignsByAccountIds($yaCampaignIds);

            foreach ($accountsCampaigns as $accountId => $accountYaCampaignIds) {

                $connection->setAuthIdentity(new ApiAccountIdentity($accounts[$accountId]));

                $criteria = new AdSelectionCriteria();
                $criteria->campaignIds = $accountYaCampaignIds;
                $criteria->states = ['ON', 'SUSPENDED', 'ARCHIVED'];

                $limit = 9000;
                $offset = 0;

                while (true) {

                    $query = new AdQuery($criteria);
                    $query->setLimit($limit);
                    $query->setOffset($offset);

                    $this->logger->log('Тело запроса: ' . json_encode($query->getQuery()));

                    try {
                        $findResult = $this->adResource->find($query);
                    } catch (\Exception $e) {
                        echo $this->logger->log('ERROR!!! ' . $e->getMessage());
                        return;
                    }

                    $offset += $limit;

                    if (!$findResult->count()) {
                        break;
                    }

                    $this->logger->log('Количество полученных от yandex.direct объявлений: ' . $findResult->count());

                    $yandexAds = $findResult->getItems();
                    $yandexAds = ArrayHelper::index($yandexAds, 'Id');

                    $toRestoreAds = array_filter($yandexAds, function ($yandexAd) {
                        return in_array($yandexAd['State'], ['SUSPENDED', 'ARCHIVED']);
                    });

                    $toSuspendAds = array_filter($yandexAds, function ($yandexAd) {
                        return $yandexAd['State'] == 'ON';
                    });

                    $this->restoreAds($toRestoreAds);
                    $this->suspendAds($toSuspendAds);
                }
            }
        }

        $this->logger->log('Синхронизация закончена');
        $this->logger->log('===============================================================================');
        $this->logger->log("Восстановлено {$this->totalRestoreCount} объявлений");
        $this->logger->log("Снято с показа {$this->totalSuspendCount} объявлений");
        $this->logger->log("Заархивировано {$this->totalArchivedCount} объявлений");
        $this->logger->log("Удалено {$this->totalDeletedCount} объявлений");
        $this->logger->log('===============================================================================');
    }

    /**
     * Остановка и снятие с показа объявлений
     *
     * @param array $yandexAds
     */
    protected function suspendAds(array $yandexAds)
    {
        $yandexAdIds = array_keys($yandexAds);

        if (empty($yandexAdIds)) {
            $this->logger->log('Нет объявлений для снятия');
            return;
        }
        $this->logger->log('Объявления для остановки показа: ' . implode(', ', $yandexAdIds));

        $myYandexAdIds = AdYandexCampaign::find()
            ->select('yandex_ad_id')
            ->andWhere([
                'yandex_ad_id' => $yandexAdIds,
                'is_published' => 1
            ])
            ->column();

        $toDeleteAdIds = array_diff($yandexAdIds, $myYandexAdIds);

        if (empty($toDeleteAdIds)) {
            $this->logger->log('Нет объявлений для снятия с показа');
            return;
        }

        $this->logger->log('Количество объявлений для удаления: ' . count($toDeleteAdIds));

        $this->logger->log('Начинаем снятие с показа...');
        $suspendResult = $this->adResource->suspend($toDeleteAdIds);

        if (!$suspendResult->isSuccess()) {
            $this->logger->log('При снятии с показа возникли ошибки: ' . json_encode($suspendResult->getErrors()));
        }

        $this->logger->log('Следующие объявления были сняты с показа: ' . implode(', ', $suspendResult->getIds()));

        $this->logger->log('Завершение снятия объявлений.');
    }

    /**
     * Восстановление объявлений
     *
     * @param array $adItems
     */
    protected function restoreAds(array $adItems)
    {
        if (empty($adItems)) {
            $this->logger->log('Нет объявлений для восстановления');
            return;
        }

        $yandexAdIds = array_keys($adItems);

        $yandexAdIdsToResume = AdYandexCampaign::find()
            ->select('yandex_ad_id')
            ->innerJoin('ad', 'ad.id = ad_yandex_campaign.ad_id')
            ->andWhere([
                'yandex_ad_id' => $yandexAdIds,
                'is_published' => 1,
                'ad.is_deleted' => 0
            ])
            ->column();

        $toUnarchive = [];
        $toResume = [];

        foreach ($yandexAdIdsToResume as $yandexId) {
            $yandexAdItem = $adItems[$yandexId];
            if ($yandexAdItem['State'] == 'ARCHIVED') {
                $toUnarchive[] = $yandexId;
            } elseif ($yandexAdItem['State'] == 'SUSPENDED') {
                $toResume[] = $yandexId;
            }
        }

        if (!empty($toUnarchive)) {
            $this->logger->log('Есть объявления для разархивирования: ' . json_encode($toUnarchive));
            $unarchiveResult = $this->adResource->unarchive($toUnarchive);
            if (!$unarchiveResult->isSuccess()) {
                $this->logger->log('При разархивировании возникли ошибки: ' . json_encode($unarchiveResult->getErrors()));
            }
            foreach ($unarchiveResult->getResult() as $item) {
                if (!$item->hasError()) {
                    $toResume[] = $item->getId();
                }
            }
        }

        if (!empty($toResume)) {
            $this->logger->log('Объявления для восстановления показов:' . json_encode($toResume));
            $this->logger->log('Количество: ' . count($toResume));
            $resumeResult = $this->adResource->resume($toResume);
            if (!$resumeResult->isSuccess()) {
                $this->logger->log('При восстановлении показов возникли следующие ошибки: ' . json_encode($resumeResult->getErrors()));
            }
            $resumeAdIds = [];
            $successCount = 0;
            foreach ($resumeResult->getResult() as $item) {
                if ($item->isOk()) {
                    $successCount++;
                    $resumeAdIds[] = $item->getId();
                }
            }
            $this->logger->log('Количество восстановленных объявлений: ' . $successCount);
            $this->logger->log('Ид восстановленных объявлений: ' . implode(', ', $resumeAdIds));
            $this->totalRestoreCount += $successCount;
        }
    }

    /**
     * Синхронизация статусов
     *
     * @param null $shopId
     */
    public function actionSyncStatus($shopId = null)
    {
        $logger = new Logger();
        $logger->log('Начинаем синхронизацию статусов с директом');
        (new AdStatusUpdater($logger))->sync($shopId);
        $logger->log('Синхронизация завершена');
    }

    /**
     * @param null $shopId
     */
    public function actionSyncKeywords($shopId = null)
    {
        $logger = new Logger();
        $keywordsSyncService = new KeywordsSyncService($logger);
        $keywordsSyncService->updateYandexId($shopId);
    }
}
