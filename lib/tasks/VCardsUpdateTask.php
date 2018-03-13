<?php
/**
 * Project Golden Casino.
 */

namespace app\lib\tasks;

use app\lib\api\auth\ApiAccountIdentity;
use app\lib\api\yandex\direct\exceptions\YandexException;
use app\lib\api\yandex\direct\query\ResultItem;
use app\lib\api\yandex\direct\resources\AdResource;
use app\lib\api\yandex\direct\resources\VCardsResource;
use app\lib\LoggedStub;
use app\lib\services\AdService;
use app\lib\services\VcardsService;
use app\models\AdYandexCampaign;
use app\models\Vcard;
use app\models\YandexCampaign;
use app\models\YandexUpdateLog;
use app\helpers\ArrayHelper;

/**
 * Class VCardsUpdateTask
 * @package app\lib\tasks
 */
class VCardsUpdateTask extends YandexBaseTask
{
    const TASK_NAME = 'VCardsUpdate';

    /**
     * @var VcardsService
     */
    protected $vCardsService;

    /**
     * @var AdService
     */
    protected $adService;

    /**
     * @var array
     */
    protected $oldVcardIds = [];
    
    /**
     * @inheritdoc
     */
    protected function init()
    {
        parent::init();
        $this->vCardsService = new VcardsService(new VCardsResource($this->connection));
        $this->adService = new AdService(new AdResource($this->connection));
    }

    /**
     * @return YandexCampaign[]
     */
    protected function updateCampaignVCardsAngGetUpdated()
    {
        /** @var YandexCampaign[] $campaigns */
        $campaigns = YandexCampaign::find()->andWhere(['shop_id' => $this->task->shop_id])->all();
        /** @var Vcard $vcard */
        $vcard = Vcard::find()->andWhere(['shop_id' => $this->task->shop_id])->one();

        $campaignsToUpdate = [];
        //обновление визитных карточек кампаний
        foreach ($campaigns as $campaign) {
            try {
                $this->setAccountToken($campaign->account_id);
                $vCardId = $this->vCardsService->createCardFor($campaign, $vcard);
                if ($vCardId) {
                    $this->oldVcardIds[$campaign->account_id][] = $campaign->yandex_vcard_id;
                    $campaign->yandex_vcard_id = $vCardId;
                    $campaignsToUpdate[] = $campaign;
                    $campaign->save();
                }
                $this->logOperation($vcard, 'create vcard');
            } catch (YandexException $e) {
                $this->logOperation($vcard, 'create vcard', YandexUpdateLog::STATUS_ERROR, $e->getMessage());
            }
        }

        return $campaignsToUpdate;
    }
    
    /**
     * @inheritDoc
     */
    public function execute($params = [])
    {
        $campaigns = $this->updateCampaignVCardsAngGetUpdated();
        if (empty($campaigns)) {
            return;
        }

        //обновление визитки в объявлениях
        foreach ($campaigns as $campaign) {
            $this->setAccountToken($campaign->account_id);
            $adQuery = AdYandexCampaign::find()
                ->joinWith(['ad.product'])
                ->andWhere(['>', 'yandex_ad_id', 0])
                ->andWhere([
                    'yandex_campaign_id' => $campaign->id,
                    'is_available' => 1
                ]);

            foreach ($adQuery->batch(500) as $ads) {
                /** @var AdYandexCampaign[] $ads */
                $ads = ArrayHelper::index($ads, 'yandex_ad_id');
                $result = $this->adService->updateVCard(array_keys($ads), $campaign->yandex_vcard_id);
                
                /**
                 * @var int $ind
                 * @var  ResultItem $item
                 */
                foreach ($result as $ind => $item) {
                    if ($item->hasError()) {
                        $error = $item->firstError();
                        if (!$item->getId()) {
                            $model = $ads[$item->getId()];
                        } else {
                            $model = new LoggedStub([
                                'type' => 'ad',
                                'id' => array_values($ads)[$ind]->id
                            ]);
                        }
                        $this->logOperation($model, 'update vcard', YandexUpdateLog::STATUS_ERROR, $error->errorInfo());
                    } else {
                        $this->logOperation($ads[$item->getId()], 'update vcard', YandexUpdateLog::STATUS_SUCCESS);
                    }
                }
            }
        }

        $vcard = Vcard::find()->andWhere(['shop_id' => $this->task->shop_id])->one();
        
        foreach ($this->oldVcardIds as $accountId => $oldVcardIds) {
            try {
                $this->setAccountToken($accountId);
                $this->vCardsService->delete($oldVcardIds);
                $this->logOperation($vcard, 'delete old vcard');
            } catch (YandexException $e) {
                $this->logOperation($vcard, 'delete old vcard', YandexUpdateLog::STATUS_ERROR, $e->getMessage());
            }
        }
    }
}
