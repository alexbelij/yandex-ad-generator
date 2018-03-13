<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 05.11.16
 * Time: 12:19
 */

namespace app\lib\services;
use app\helpers\ArrayHelper;
use app\helpers\StringHelper;
use app\lib\Logger;
use app\lib\PointsCalculator;
use app\lib\variationStrategies\DefaultStrategy;
use app\models\Account;
use app\models\Ad;
use app\models\AdYandexCampaign;
use app\models\BrandAccount;
use app\models\CampaignTemplate;
use app\models\ExternalProduct;
use app\models\Forecast;
use app\models\GeneratorSettings;
use app\models\search\ProductsSearch;
use app\models\search\YandexAdUpdateSearch;
use app\models\Shop;
use app\models\YandexCampaign;
use yii\base\Exception;

/**
 * Прогноз потраченных баллов
 *
 * Class PointsForecast
 * @package app\lib\services
 */
class PointsForecast
{
    const FAULT_POINTS = 140;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var PointsCalculator[]
     */
    protected $brandPoints = [];

    /**
     * @var Shop
     */
    protected $shop;

    /**
     * Подсчитывает и возвращает примерный расход баллов за операцию обновления по брендам
     *
     * @param Shop $shop
     * @return \app\lib\PointsCalculator[]
     */
    public function calculateFor(Shop $shop)
    {
        $generatorSettings = GeneratorSettings::forShop($shop->id);
        $searchModel = new YandexAdUpdateSearch();
        $brandIds = explode(',', $generatorSettings->brands);

        $query = $searchModel->search([
            'shop' => $shop,
            'brandIds' => $brandIds,
            'categoryIds' => $generatorSettings->categoryIds,
            'priceFrom' => $generatorSettings->price_from,
            'priceTo' => $generatorSettings->price_to
        ]);
        $this->shop = $shop;
        $yandexCampaignCreate = [];

        /** @var CampaignTemplate[] $campaignTemplates */
        $campaignTemplates = CampaignTemplate::find()
            ->andWhere(['shop_id' => $this->shop->primaryKey])
            ->all();

        $counts = [
            'create' => 0,
            'update' => 0,
            'suspend' => 0,
            'resume' => 0,
            'ad' => 0,
            'pass' => 0,
            'pass_phrase' => 0
        ];
        $passBrands = [];
        $passProductIds = [];

        $logger = $this->getLogger();

        /** @var Ad[] $ads */
        foreach ($query->batch(500) as $ads) {
            $externalProducts = $this->getExternalProducts(ArrayHelper::getColumn($ads, 'product.product_id'));
            foreach ($ads as $ad) {
                $counts['ad']++;
                $adProduct = $ad->product;
                if (trim($ad->title) == DefaultStrategy::LIMIT_REACH_MESSAGE) {
                    $counts['pass_phrase']++;
                    continue;
                }
                /** @var ExternalProduct|null $extProduct */
                $extProduct = array_key_exists($ad->product->product_id, $externalProducts) ?
                    $externalProducts[$ad->product->product_id] : null;

                if (!$extProduct) {
                    if (!in_array($ad->product->brand_id, $passBrands)) {
                        $passBrands[] = $ad->product->brand_id;
                    }
                    $passProductIds[] = $ad->product->product_id;
                    continue;
                }

                $pointsCalculator = $this->getPointsCalculator($ad->product->brand_id);
                $adKeywordsCount = count(StringHelper::explodeByDelimiter($ad->keywords));
                $account = BrandAccount::getAccountByBrand($this->shop, $ad->product->brand_id);
                foreach ($campaignTemplates as $campaignTemplate) {
                    if (!$campaignTemplate->isAllowForBrand($ad->product->brand_id)) {
                        continue;
                    }
                    $yandexCampaignIds = $this->getYandexCampaigns($ad, $campaignTemplate, $account);
                    /** @var AdYandexCampaign|null $yandexAd */
                    $yandexAds = [];
                    if ($yandexCampaignIds) {
                        $yandexAds = AdYandexCampaign::find()->andWhere([
                            'ad_id' => $ad->primaryKey,
                            'yandex_campaign_id' => $yandexCampaignIds,
                            'account_id' => $account->id
                        ])->all();
                    }

                    if (!empty($yandexAds)) {
                        foreach ($yandexAds as $yandexAd) {
                            if (empty($yandexAd->yandex_ad_id) && $extProduct->is_available) {
                                //публикация нового объявления
                                $pointsCalculator->inc(PointsCalculator::ADS, PointsCalculator::OP_ADD, 1);
                                $pointsCalculator->inc(PointsCalculator::ADGROUPS, PointsCalculator::OP_ADD, 1);
                                $pointsCalculator->inc(PointsCalculator::KEYWORDS, PointsCalculator::OP_ADD, $adKeywordsCount);
                                $counts['create']++;
                                $this->logger->log("Публикация объявления: {$yandexAd->ad->title}, id: {$yandexAd->ad->id}");
                                //снятие объявления
                            } elseif ($adProduct->is_available && $yandexAd->is_published && !$extProduct->is_available) {
                                $pointsCalculator->inc(PointsCalculator::ADS, PointsCalculator::OP_SUSPEND, 1);
                                $counts['suspend']++;
                                $this->logger->log("Снимаем объявление: {$yandexAd->ad->title}, id: {$yandexAd->ad->id}");
                                //обновление объявления
                            } elseif ($this->isNeedUpdateAd($yandexAd, $extProduct)) {
                                $pointsCalculator->inc(PointsCalculator::ADS, PointsCalculator::OP_UPDATE, 1);
                                $pointsCalculator->inc(PointsCalculator::KEYWORDS, PointsCalculator::OP_GET, 1);
                                $this->logger->log("Обновление объявления: {$yandexAd->ad->title}, id: {$yandexAd->ad->id}");
                                $counts['update']++;
                                //восстановление показов
                            } elseif (!$yandexAd->is_published && $extProduct->is_available) {
                                $pointsCalculator->inc(PointsCalculator::ADS, PointsCalculator::OP_RESUME, 1);
                                $pointsCalculator->inc(PointsCalculator::ADS, PointsCalculator::OP_UPDATE, 1);
                                $this->logger->log("Восстановление объявления: {$yandexAd->ad->title}, id: {$yandexAd->ad->id}");
                                $counts['resume']++;
                            } else {
                                $counts['pass']++;
                            }
                        }
                    }  elseif ($extProduct->is_available) {

                        //создание новой кампании
                        if (empty($yandexCampaignCreate[$campaignTemplate->id])) {
                            $pointsCalculator->inc(PointsCalculator::CAMPAIGNS, PointsCalculator::OP_ADD, 1);
                            $yandexCampaignCreate[$campaignTemplate->id] = 1;
                        }

                        //публикация нового объявления
                        $pointsCalculator->inc(PointsCalculator::ADS, PointsCalculator::OP_ADD, 1);
                        $pointsCalculator->inc(PointsCalculator::ADGROUPS, PointsCalculator::OP_ADD, 1);
                        $pointsCalculator->inc(PointsCalculator::KEYWORDS, PointsCalculator::OP_ADD, $adKeywordsCount);
                        $counts['create']++;
                    } else {
                        $counts['pass']++;
                    }
                }
            }
        }

        $result = [];
        foreach ($brandIds as $brandId) {
            $result[$brandId] = isset($this->brandPoints[$brandId]) && $this->brandPoints[$brandId]->getTotal()?
                $this->brandPoints[$brandId]->getTotal() + self::FAULT_POINTS: 0;
        }

        $logger->log('Всего объявлений обработано: ' . $counts['ad']);
        $logger->log('Объявлений будет создано:' . $counts['create']);
        $logger->log('Объявлений будет обновлено: ' . $counts['update']);
        $logger->log('Объявлений будет восстановлено: ' . $counts['resume']);
        $logger->log('Объявлений будет снято: ' . $counts['suspend']);
        $logger->log('Объявлений пропущено: ' . $counts['pass']);
        $logger->log('Объявлений пропущено (неправильный заголовок): ' . $counts['pass_phrase']);

        if (!empty($passBrands)) {
            $logger->log('Пропущены товары брендов: ' . implode(', ', $passBrands));
        }

        foreach ($this->brandPoints as $brandId => $brandPoint) {
            $this->logger->log("brand: $brandId, " . json_encode($brandPoint->getPoints()));
        }

//        if (!empty($passProductIds)) {
//            $logger->log('Пропущены товары с ид:' . implode(', ', $passProductIds));
//        }

        return $result;
    }

    /**
     * Обновление прогноза расходования баллов для магазина
     *
     * @param Shop $shop
     * @throws Exception
     */
    public function update(Shop $shop)
    {
        $points = $this->calculateFor($shop);
        foreach ($points as $brandId => $value) {
            $forecast = Forecast::find()
                ->andWhere([
                    'shop_id' => $shop->id,
                    'brand_id' => $brandId
                ])->one();

            if (!$forecast) {
                $forecast = new Forecast([
                    'shop_id' => $shop->id,
                    'brand_id' => $brandId,
                ]);
            }

            $forecast->points = $value;
            if (!$forecast->save()) {
                $errors = $forecast->getErrors();
                throw new Exception(reset($errors));
            }
        }
    }

    /**
     * @param AdYandexCampaign $yandexAd
     * @param ExternalProduct $externalProduct
     * @return bool
     */
    protected function isNeedUpdateAd(AdYandexCampaign $yandexAd, ExternalProduct $externalProduct)
    {
        $product = $yandexAd->ad->product;
        //обновляем только товары, которые есть в наличии
        if (!$product->is_available ||
            !$externalProduct->is_available ||
            !$yandexAd->yandex_ad_id ||
            !$yandexAd->is_published
        ) {
            return false;
        }

        if ($product->isAutomaticPrice() && $product->price != $externalProduct->price) {
            $this->getLogger()->log(
                "Цена для товара: id = {$product->id} отличается от оригинальной. {$product->price} != {$externalProduct->price}"
            );
            return true;
        } elseif (strtotime($yandexAd->ad->updated_at) > strtotime($yandexAd->uploaded_at)) {
            $this->getLogger()->log(
                "Обновление не обновлялось после изменения: {$yandexAd->ad->updated_at} и {$yandexAd->uploaded_at}"
            );
            return true;
        }

        return false;
    }

    /**
     * @param array $extProductIds
     * @return ExternalProduct[]
     */
    protected function getExternalProducts(array $extProductIds)
    {
        return ExternalProduct::find()
            ->andWhere([
                'id' => $extProductIds,
                'shop_id' => $this->shop->id
            ])
            ->indexBy('id')
            ->all();
    }

    /**
     * @param int $brandId
     * @return PointsCalculator
     */
    protected function getPointsCalculator($brandId)
    {
        if (!array_key_exists($brandId, $this->brandPoints)) {
            $this->brandPoints[$brandId] = new PointsCalculator();
        }

        return $this->brandPoints[$brandId];
    }

    /**
     * @param Ad $ad
     * @param CampaignTemplate $campaignTemplate
     * @param Account $account
     * @return int[]
     */
    protected function getYandexCampaigns(Ad $ad, CampaignTemplate $campaignTemplate, Account $account)
    {
        return YandexCampaign::find()
            ->select('id')
            ->andWhere([
                'shop_id' => $this->shop->id,
                'brand_id' => $ad->product->brand_id,
                'campaign_template_id' => $campaignTemplate->id,
                'account_id' => $account->id
            ])
            ->column();
    }

    /**
     * @return Logger
     */
    public function getLogger()
    {
        if (is_null($this->logger)) {
            $this->logger = new Logger();
        }

        return $this->logger;
    }

    /**
     * @param Logger $logger
     * @return PointsForecast
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
        return $this;
    }
}
