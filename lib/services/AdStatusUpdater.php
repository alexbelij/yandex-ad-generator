<?php

namespace app\lib\services;

use app\components\LoggerInterface;
use app\helpers\ArrayHelper;
use app\lib\api\auth\ApiAccountIdentity;
use app\lib\api\yandex\direct\Connection;
use app\lib\api\yandex\direct\query\ad\AdSelectionCriteria;
use app\lib\api\yandex\direct\query\adGroup\AdGroupSelectionCriteria;
use app\lib\api\yandex\direct\query\AdGroupQuery;
use app\lib\api\yandex\direct\query\AdQuery;
use app\lib\api\yandex\direct\resources\AdGroupResource;
use app\lib\api\yandex\direct\resources\AdResource;
use app\models\Account;
use app\models\AdYandexCampaign;

/**
 * Class AdStatusUpdater
 * @package app\lib\services
 */
class AdStatusUpdater
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * AdStatusUpdater constructor.
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

    /**
     * Синхронизация статусов с директом
     *
     * @param null $shopId
     */
    public function sync($shopId = null)
    {
        /** @var Account $accounts */
        $accounts = Account::find()->all();

        foreach ($accounts as $account) {
            $query = AdYandexCampaign::find()
                ->innerJoin('ad', 'ad.id = ad_yandex_campaign.ad_id')
                ->innerJoin('product', 'product.id = ad.product_id')
                ->andWhere('yandex_ad_id IS NOT NULL')
                ->andWhere(['account_id' => $account->id])
                ->andFilterWhere(['product.shop_id' => $shopId]);

            $connection = new Connection(new ApiAccountIdentity($account));
            $connection->setTimeout(30);
            $adGroupResource = new AdGroupResource($connection);
            $adResource = new AdResource($connection);

            $this->log("Начинаем синхронизацию объявлений для аккаунта - {$account->title}");

            foreach ($query->batch(9000) as $adYandexCampaigns) {
                $this->update($adYandexCampaigns, $adGroupResource, $adResource);
            }
        }
    }

    /**
     * Синхронизация статусов и состояний объявлений с директом
     *
     * @param AdYandexCampaign[] $adYandexCampaigns
     * @param AdGroupResource $adGroupResource
     * @param AdResource $adResource
     */
    public function update($adYandexCampaigns, AdGroupResource $adGroupResource, AdResource $adResource)
    {
        $criteria = new AdSelectionCriteria([
            'ids' => ArrayHelper::getColumn($adYandexCampaigns, 'yandex_ad_id')
        ]);
        $adQuery = new AdQuery($criteria);

        $yandexAdsResult = $adResource->find($adQuery);

        if (!$yandexAdsResult->count()) {
            return;
        }

        $this->log('От директа получено ' . $yandexAdsResult->count() . ' объявлений');

        $yandexAds = $yandexAdsResult->getItems();
        $yandexAds = ArrayHelper::index($yandexAds, 'Id');

        $adGroupCriteria = new AdGroupSelectionCriteria([
            'ids' => ArrayHelper::getColumn($adYandexCampaigns, 'yandex_adgroup_id')
        ]);
        $adGroupQuery = new AdGroupQuery($adGroupCriteria);

        $adYandexGroupResult = $adGroupResource->find($adGroupQuery);
        $adYandexGroups = $adYandexGroupResult->getItems();

        if (!empty($adYandexGroups)) {
            $adYandexGroups = ArrayHelper::index($adYandexGroups, 'Id');
        }

        $this->log('От директа получено ' . count($adYandexGroups) . ' групп');

        foreach ($adYandexCampaigns as $adYandexCampaign) {
            $yandexId = $adYandexCampaign->yandex_ad_id;
            if (!isset($yandexAds[$yandexId])) {
                continue;
            }

            $yandexAd = $yandexAds[$yandexId];

            $state = strtolower($yandexAd['State']);
            $status = strtolower($yandexAd['Status']);

            $adYandexGroup = null;
            if (isset($adYandexGroups[$adYandexCampaign->yandex_adgroup_id])) {
                $adYandexGroup = $adYandexGroups[$adYandexCampaign->yandex_adgroup_id];
            }

            $updateData = [];

            if ($state != $adYandexCampaign->state) {
                $updateData['state'] = $state;
            }

            if ($status != $adYandexCampaign->status) {
                $updateData['status'] = $status;
            }

            if ($adYandexGroup && $adYandexGroup['Name'] != $adYandexCampaign->yandex_group_name) {
                $updateData['yandex_group_name'] = $adYandexGroup['Name'];
            }

            if (!empty($updateData)) {
                $this->log(
                    'Обновление информации для объявления: adId: ' .
                    $adYandexCampaign->ad_id . ', title: ' . $adYandexCampaign->ad->title
                );
                AdYandexCampaign::updateAll($updateData, ['yandex_ad_id' => $yandexId]);
            }
        }
    }

    /**
     * Логирование операций
     *
     * @param string $message
     */
    protected function log($message)
    {
        if ($this->logger) {
            $this->logger->log($message);
        }
    }
}
