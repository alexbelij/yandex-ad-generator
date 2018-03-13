<?php

namespace app\commands;

use app\components\LoggerInterface;
use app\helpers\ArrayHelper;
use app\lib\Logger;
use app\models\Ad;
use app\models\AdKeyword;
use app\models\AdYandexCampaign;
use app\models\AdYandexGroup;
use app\models\YandexCampaign;
use yii\console\Controller;
use yii\console\Exception;

/**
 * Class KeywordsCreateController
 * @package app\commands
 */
class KeywordsCreateController extends Controller
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @inheritDoc
     */
    public function init()
    {
        parent::init();
        $this->logger = new Logger();
    }

    /**
     * @param null|int $shopId
     */
    public function actionIndex($shopId = null)
    {
        $query = YandexCampaign::find();

        if ($shopId) {
            $query->andWhere(['shop_id' => $shopId]);
        }

        $campaigns = $query->all();
        $this->logger->log('Получено кампаний: ' . count($campaigns));

        /**
         * @var int $i
         * @var YandexCampaign $campaign
         */
        foreach ($campaigns as $i => $campaign) {
            $query = AdYandexCampaign::find()
                ->andWhere(['yandex_campaign_id' => $campaign->primaryKey])
                ->joinWith(['ad']);

            $batch = clone $query;

            $this->logger->log("Обработка кампании $i => $campaign->title");
            $this->logger->log('Количество объявлений в кампании: ' . $query->count());

            foreach ($batch->each(500) as $campaignAd) {
                /** @var Ad $ad */
                $ad = $campaignAd->ad;
                $keywords = explode("\r\n", $ad->keywords);

                foreach ($keywords as $keyword) {
                    try {
                        $keyword = new AdKeyword([
                            'ad_id' => $ad->primaryKey,
                            'keyword' => trim($keyword),
                            'is_generated' => $ad->is_auto
                        ]);
                        $keyword->save();
                    } catch (\Exception $e) {
                        $this->logger->log($e->getMessage());
                    }
                }

                $group = new AdYandexGroup([
                    'yandex_adgroup_id' => (string)$campaignAd->yandex_adgroup_id,
                    'keywords_count' => count($keywords),
                    'ads_count' => 1,
                    'yandex_campaign_id' => $campaign->primaryKey,
                    'status' => AdYandexGroup::STATUS_ACCEPTED,
                    'serving_status' => AdYandexGroup::SERVING_STATUS_ELIGIBLE,
                ]);

                if ($group->save()) {
                    $campaignAd->ad_yandex_group_id = $group->primaryKey;
                    $campaignAd->save();
                } else {
                    throw new Exception(ArrayHelper::first($group->getFirstErrors()));
                }
            }
        }
    }

    public function actionFixFail()
    {
        $query = Ad::find()
            ->leftJoin(['ak' => 'ad_keyword'], 'ak.ad_id = ad.id')
            ->andWhere(['ak.id' => null]);

        $this->logger->log('Начинаем добавление отсутствующих ключевых фраз');
        $this->logger->log('Получено объявлений для обработки:' . $query->count());

        /** @var Ad $ad */
        foreach ($query->each(500) as $ad) {
            $keywords = array_filter(explode("\r\n", $ad->keywords));
            foreach ($keywords as $keyword) {
                $adKeyword = new AdKeyword([
                    'keyword' => $keyword,
                    'ad_id' => $ad->primaryKey,
                    'is_generated' => $ad->is_auto
                ]);

                if (!$adKeyword->save()) {
                    $this->logger->log(ArrayHelper::first($adKeyword->getFirstErrors()));
                }
            }
        }
        $this->logger->log('Операция завершена');
    }
}
