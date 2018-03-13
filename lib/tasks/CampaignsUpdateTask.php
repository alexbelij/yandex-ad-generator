<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 28.04.16
 * Time: 21:46
 */

namespace app\lib\tasks;

use app\lib\api\yandex\direct\exceptions\YandexException;
use app\lib\api\yandex\direct\resources\CampaignResource;
use app\lib\services\YandexCampaignService;
use app\models\YandexCampaign;
use app\models\YandexUpdateLog;

class CampaignsUpdateTask extends YandexBaseTask
{
    const TASK_NAME = 'campaignsUpdate';

    /**
     * @var YandexCampaignService
     */
    protected $campaignService;

    protected function init()
    {
        parent::init();

        $campaignResource = new CampaignResource($this->connection);

        $this->campaignService = new YandexCampaignService($campaignResource);
    }
    
    /**
     * @inheritDoc
     */
    public function execute($params = [])
    {
        /** @var YandexCampaign[] $campaigns */
        $campaigns = YandexCampaign::find()
            ->andWhere([
                'shop_id' => $this->shop->id,
                'account_id' => $this->shop->account_id
            ])
            ->all();
        
        foreach ($campaigns as $campaign) {
            try {
                $this->setAccountToken($campaign->account_id);
                $this->campaignService->updateCampaign($campaign->yandex_id);
                $this->logOperation($campaign, YandexUpdateLog::OPERATION_UPDATE);
            } catch (YandexException $e) {
                $this->logOperation($campaign, YandexUpdateLog::OPERATION_UPDATE, YandexUpdateLog::STATUS_ERROR, $e->getMessage());
            }
        }
    }
}
