<?php

namespace app\lib\shuffleGroupStrategies;

use app\helpers\ArrayHelper;
use app\helpers\JsonHelper;
use app\lib\api\shop\gateways\InternalProductsGateway;
use app\lib\api\shop\models\ExtProduct;
use app\lib\api\yandex\direct\resources\AdGroupResource;
use app\lib\api\yandex\direct\resources\AdResource;
use app\lib\api\yandex\direct\resources\KeywordsResource;
use app\lib\services\AdGroupService;
use app\lib\services\AdService;
use app\lib\services\KeywordsService;
use app\models\AdYandexCampaign;
use app\models\AdYandexGroup;
use app\models\YandexUpdateLog;

/**
 * Class ShuffleDefault
 * @package app\lib\shuffleGroupStrategies
 */
class ShuffleDefault extends AbstractShuffleGroupStrategy
{
    /**
     * @var array
     */
    protected $processedGroups = [];

    /**
     * @var AdGroupService
     */
    protected $adGroupService;

    /**
     * @var AdService
     */
    protected $adService;

    /**
     * @var KeywordsService
     */
    protected $keywordsService;

    /**
     * @var InternalProductsGateway
     */
    protected $productsGateway;

    /**
     * @inheritdoc
     */
    protected function init()
    {
        parent::init();

        $adResource = new AdResource($this->connection);
        $adGroupResource = new AdGroupResource($this->connection);
        $keywordsResource = new KeywordsResource($this->connection);

        $this->adGroupService = new AdGroupService($adGroupResource);
        $this->adService = new AdService($adResource);
        $this->keywordsService = new KeywordsService($keywordsResource);
        $this->productsGateway = InternalProductsGateway::factory($this->shop);
    }

    /**
     * @inheritDoc
     */
    public function execute($groups)
    {
        $this->adService->setLogger($this->logger);
        $this->keywordsService->setLogger($this->logger);

        foreach ($groups as $group) {
            $this->shuffleAds($group);
        }

        foreach ($this->processedGroups as $groupId => $ids) {
            if (!$ids) {
                continue;
            }

            $group = AdYandexGroup::findOne($groupId);

            if (!$group) {
                continue;
            }

            $this->logger->log('Обработка затронутой группы ' . $groupId);

            try {
                $this->updateAds($group);
            } catch (\Exception $e) {
                $this->logger->log(
                    'Ошибка при обработке затронутой группы: ' . $e->getMessage()
                );
            }
        }

    }

    /**
     * @param AdYandexGroup $group
     */
    private function shuffleAds(AdYandexGroup $group)
    {
        $this->logger->log(
            'Обработка группы: ' . json_encode($group->toArray(), JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT)
        );

        /**
         * @var AdYandexCampaign[] $yandexAds
         */
        $yandexAds = $group->getYandexAds()
            ->andWhere([AdYandexCampaign::fullColumn('is_published') => 1])
            ->joinWith([
                'ad.adKeywords',
                'ad.product',
            ])
            ->all();

        $keywords = $this->keywordsService->filterKeywords($group->getKeywords(), true);
        $maxLen = max(
            array_map(
                function ($keyword) {
                    return mb_strlen($keyword, 'UTF-8');
                },
                $keywords
            ), 0
        );
        $ignoreMaxLen = 0;

        foreach ($yandexAds as $yandexAd) {
            $transaction = \Yii::$app->db->beginTransaction();
            $hasError = false;
            $defineIgnoreMaxLen = false;
            $group->refresh();

            $this->logger->log(
                'Обработка объявления: ' . json_encode($yandexAd->toArray(), JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)
            );

            try {
                $resultKeyword = null;

                $keywords = $this->keywordsService->filterKeywords(
                    ArrayHelper::getColumn($yandexAd->ad->adKeywords, 'keyword'),
                    true
                );

                foreach ($keywords as $i => $keyword) {
                    if (mb_strlen($keyword) < KeywordsService::MAX_KEYWORD_LENGTH) {
                        $resultKeyword = $keyword;
                        break;
                    }
                }

                $keywordsCount = count($yandexAd->ad->adKeywords);
                $this->logger->log('Количество ключевых фраз: ' . $keywordsCount);
                $this->logger->log(
                    'Ключевые фразы после filterKeywords: ' .
                    json_encode($keywords, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                );

                if (!$ignoreMaxLen && count($keywords) > 1) {
                    foreach ($keywords as $i => $keyword) {
                        if (mb_strlen($keyword, 'UTF-8') == $maxLen) {
                            $ignoreMaxLen = $maxLen;
                            $defineIgnoreMaxLen = true;
                            unset($keywords[$i]);
                            break;
                        }
                    }
                }

                if (!$keywords || !$resultKeyword) {
                    throw new \Exception(
                        'Ошибка при сохрании новой группы: не удалось подобрать ключевое слово для смены заголовка' .
                        $yandexAd->primaryKey
                    );
                }

                $this->logger->log(
                    'Объявление: ' .
                    json_encode($yandexAd->ad->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                );

                $suitableGroup = AdYandexGroup::findSuitable($yandexAd, $keywordsCount);

                if (!$suitableGroup) {
                    $groupId = $this->adGroupService->createAdGroup($yandexAd);
                    $suitableGroup = new AdYandexGroup([
                        'yandex_adgroup_id' => $groupId,
                        'yandex_campaign_id' => $yandexAd->yandex_campaign_id,
                        'ads_count' => 0,
                        'keywords_count' => 0,
                    ]);
                }

                if (!isset($this->processedGroups[$suitableGroup->primaryKey])) {
                    $this->processedGroups[$suitableGroup->primaryKey] = [];
                }

                $suitableGroup->keywords_count += $keywordsCount;

                if (!$suitableGroup->save()) {
                    throw new \Exception(
                        'Ошибка при сохрании новой группы: ' . implode('; ', $suitableGroup->getFirstErrors())
                    );
                }

                $group->keywords_count -= $keywordsCount;

                if (!$group->save()) {
                    throw new \Exception(
                        'Ошибка при сохранении старой группы: ' . implode('; ', $group->getFirstErrors())
                    );
                }

                $yandexAd->refresh();
                $deleteResult = $this->keywordsService->deleteKeywords(
                    $yandexAd,
                    $defineIgnoreMaxLen ? $ignoreMaxLen : 0,
                    true
                );

                if ($deleteResult) {
                    $this->logger->log(
                        'Результат удаления ключевых фраз: ' . json_encode($deleteResult->getResult(), JSON_PRETTY_PRINT)
                    );
                } else {
                    $this->logger->log('Нечего удалять');
                }

                if ($deleteResult && !$deleteResult->isSuccess()) {
                    throw new \Exception(
                        'Ошибки при удалении ключевых слов: ' . implode('; ', $deleteResult->getErrors())
                    );
                }

                $yandexAd->refresh();
                $updateResult = $this->keywordsService->updateGroup(
                    $yandexAd, $suitableGroup->yandex_adgroup_id, false, true
                );

                if ($updateResult && !$updateResult->isSuccess()) {
                    throw new \Exception(
                        'Ошибки при редактировании ключевых слов: ' . implode('; ', $updateResult->getErrors())
                    );
                }

                $this->processedGroups[$suitableGroup->primaryKey][] = $yandexAd->primaryKey;
            } catch (\Exception $e) {
                $hasError = true;
                $transaction->rollBack();
                $this->logOperation(
                    $yandexAd, YandexUpdateLog::OPERATION_KEYWORDS_MOVE, YandexUpdateLog::STATUS_ERROR, $e->getMessage()
                );
                $this->logger->log(
                    'Ошибка при переносе объявление из группы в группу: ' . $e->getMessage()
                );
            }

            if (!$hasError) {
                $transaction->commit();
                $this->logOperation($yandexAd, YandexUpdateLog::OPERATION_KEYWORDS_MOVE);
                $this->logger->log(
                    'Объявление перенесено из группы ' . $group->yandex_adgroup_id . ' в группу ' .
                    $suitableGroup->yandex_adgroup_id . ': ' . JsonHelper::encodeModelPretty($yandexAd)
                );
            }
        }
    }


    /**
     * @param AdYandexGroup $group
     */
    private function updateAds(AdYandexGroup $group)
    {
        $processedIds = !empty($this->processedGroups[$group->primaryKey])
            ? $this->processedGroups[$group->primaryKey] : [];
        $query = $group->getYandexAds()
            ->joinWith([
                'ad.adKeywords',
                'ad.product',
            ]);

        if ($processedIds) {
            $query->andWhere(['NOT IN', AdYandexCampaign::tableName() . '.id', $processedIds]);
        }

        /**
         * @var AdYandexCampaign[] $ads
         */
        $ads = $query->all();

        if (!$ads) {
            return;
        }

        $productIds = ArrayHelper::getColumn($ads, 'ad.product.product_id');
        $productsFromApi = [];

        foreach ($this->productsGateway->findByIds($productIds) as $product) {
            $productsFromApi[$product['id']] = new ExtProduct($product);
        }

        foreach ($ads as $yandexAd) {
            $product = isset($productsFromApi[$yandexAd->ad->product->product_id])
                ? $productsFromApi[$yandexAd->ad->product->product_id]
                : null;

            if (!$product) {
                continue;
            }

            $resultKeyword = null;
            $keywords = $this->keywordsService->filterKeywords(
                ArrayHelper::getColumn($yandexAd->ad->adKeywords, 'keyword'),
                true
            );

            foreach ($keywords as $keyword) {
                if (mb_strlen($keyword) <= KeywordsService::MAX_KEYWORD_LENGTH) {
                    $resultKeyword = $keyword;
                    break;
                }
            }

            if ($resultKeyword) {
                $yandexAd->ad->title = $resultKeyword;
                $this->adService->update($yandexAd, $product, true);
            }
        }
    }
}
