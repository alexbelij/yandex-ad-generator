<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 10.04.16
 * Time: 21:34
 */

namespace app\lib\provider;

use app\lib\api\auth\ApiAccountIdentity;
use app\lib\api\yandex\direct\Connection;
use app\lib\api\yandex\direct\resources\CampaignResource;
use app\models\Account;
use app\models\YandexCampaign;
use yii\data\ActiveDataProvider;
use yii\log\Logger;

class ActiveCampaignProvider extends ActiveDataProvider
{
    /**
     * @var CampaignResource[]
     */
    protected static $resources = [];

    /**
     * @inheritDoc
     */
    protected function prepareModels()
    {
        /** @var YandexCampaign[] $models */
        $models = parent::prepareModels();
        if (empty($models)) {
            return $models;
        }
        
        $campaignIds = [];
        
        foreach ($models as $model) {
            $campaignIds[$model->account->primaryKey][] = $model->yandex_id;
        }

        if (empty($campaignIds)) {
            return $models;
        }

        $campaigns = $this->loadCampaigns($campaignIds);

        foreach ($models as $model) {
            if (isset($campaigns[$model->yandex_id])) {
                $model->yandexData = $campaigns[$model->yandex_id];
            }
        }

        return $models;
    }

    /**
     * @param array $campaigns
     * @return array
     */
    protected function loadCampaigns($campaigns)
    {
        $items = [];
        foreach ($campaigns as $accountId => $campaignIds) {
            $resource = $this->getCampaignResource($accountId);
            try {
                $result = $resource->findByIds($campaignIds);
            } catch (\Exception $e) {
                \Yii::getLogger()->log($e->getCode() . ': ' . $e->getMessage(), Logger::LEVEL_ERROR);
                $result = [];
            }

            foreach ($result as $item) {
                $items[$item['Id']] = $item;
            }
        }

        return $items;
    }

    /**
     * @param $accountId
     * @return CampaignResource
     */
    protected function getCampaignResource($accountId)
    {
        if (empty(self::$resources[$accountId])) {
            $account = Account::findOne($accountId);
            self::$resources[$accountId] = new CampaignResource(new Connection(new ApiAccountIdentity($account)));
        }

        return self::$resources[$accountId];
    }
}
