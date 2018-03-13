<?php

namespace app\lib\shuffleGroupStrategies;

use app\helpers\ArrayHelper;
use app\lib\api\yandex\direct\resources\BidResource;
use app\lib\api\yandex\direct\resources\KeywordsResource;
use app\lib\dto\BidDto;
use app\lib\services\BidService;
use app\lib\services\KeywordsService;
use app\models\AdKeyword;
use app\models\AdYandexCampaign;
use app\models\AdYandexGroup;
use app\models\ExternalBrand;
use app\models\YandexUpdateLog;
use yii\base\Exception;

/**
 * Class ShuffleStrategy2
 * @package app\lib\shuffleGroupStrategies
 */
class ShuffleStrategy2 extends AbstractShuffleGroupStrategy
{
    /**
     * @var BidService
     */
    protected $bidService;

    /**
     * @var KeywordsService
     */
    protected $keywordsService;

    /**
     * @inheritDoc
     */
    protected function init()
    {
        parent::init();
        $this->bidService = new BidService(new BidResource($this->connection));
        $this->keywordsService = new KeywordsService(new KeywordsResource($this->connection));
    }

    /**
     * @inheritDoc
     */
    public function execute($groups)
    {
        foreach ($groups as $group) {
            $this->_execute($group);
        }
    }

    /**
     * @param AdYandexGroup $group
     * @throws Exception
     * @throws \Exception
     */
    protected function _execute(AdYandexGroup $group)
    {
        /** @var AdYandexCampaign[] $yandexAds */
        $yandexAds = $group->getYandexAds()
            ->andWhere([AdYandexCampaign::fullColumn('is_published') => 1])
            ->joinWith([
                'ad.adKeywords',
                'ad.product',
            ])
            ->all();

        foreach ($yandexAds as $yandexAd) {

            $brandTitle = $this->getBrandTitle($yandexAd);
            $brandTitleKeyword = '"' . $brandTitle . '"';
            $keywords = $yandexAd->ad->getKeywordsList();

            //ключевая фраза в виде бренда присутствует
            if (in_array($brandTitleKeyword, $keywords)) {
                continue;
            }

            $adKeyword = new AdKeyword([
                'keyword' => $brandTitleKeyword,
                'is_generated' => false,
                'ad_id' => $yandexAd->ad_id,
            ]);

            if (!$adKeyword->save()) {
                throw new Exception(ArrayHelper::first($adKeyword->getFirstErrors()));
            }

            try {
                $keywordsResult = $this->keywordsService->createKeywordsFromArray($yandexAd, [$brandTitleKeyword]);
                $this->logOperation($yandexAd, YandexUpdateLog::OPERATION_KEYWORDS_CREATE);
            } catch (\Exception $e) {
                $this->logOperation(
                    $yandexAd,
                    YandexUpdateLog::OPERATION_KEYWORDS_CREATE,
                    YandexUpdateLog::STATUS_ERROR,
                    $e->getMessage()
                );
                throw $e;
            }

            $keywordIds = $keywordsResult->getIds();

            if (!empty($keywordIds)) {
                //установка ставок 1 рубль
                $this->updateBids($keywordIds, 1);
                $this->logOperation(
                    $yandexAd,
                    YandexUpdateLog::OPERATION_UPDATE_BIDS,
                    YandexUpdateLog::STATUS_SUCCESS,
                    'Обновление ставок для ключевых фраз: ' . $brandTitleKeyword
                );
            }
        }
    }

    /**
     * @param AdYandexCampaign $adYandexCampaign
     * @return string
     */
    protected function getBrandTitle(AdYandexCampaign $adYandexCampaign)
    {
        /** @var ExternalBrand $brand */
        $brand = $adYandexCampaign->ad->product->externalProduct->brand;

        $variation = $brand->getVariation();

        if ($variation && $variation->shuffle_name) {
            return $variation->shuffle_name;
        }

        return $brand->title;
    }

    /**
     * Обновление ставок перенесенным ключевым фразам
     *
     * @param int|int[] $keywordIds
     * @param int $bid
     * @throws \Exception
     */
    private function updateBids($keywordIds, $bid)
    {
        $bids = BidDto::createFor($keywordIds, $bid, BidDto::TYPE_KEYWORDS);
        $result = $this->bidService->updateBids($bids, BidDto::TYPE_KEYWORDS);
        if (!$result->isSuccess()) {
            throw new \Exception(
                'Ошибки при удалении ключевых слов: ' . implode('; ', $result->getErrors())
            );
        }
    }
}
