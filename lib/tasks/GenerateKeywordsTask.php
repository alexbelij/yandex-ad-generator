<?php

namespace app\lib\tasks;

use app\components\FileLogger;
use app\helpers\JsonHelper;
use app\lib\api\shop\models\ExtProduct;
use app\lib\dto\Template;
use app\lib\services\KeywordsUpdateChecker;
use app\lib\variationStrategies\VariationStrategyFactory;
use app\lib\variationStrategies\VariationStrategyInterface;
use app\models\Ad;
use app\models\AdKeyword;
use app\models\AdYandexGroup;
use app\models\AdTemplate;
use app\models\BlackList;
use app\models\Product;
use app\models\search\ProductsSearch;
use app\models\TaskQueue;
use yii\db\Query;
use app\helpers\ArrayHelper;

/**
 * Class GenerateKeywordsTask
 * @package app\lib\tasks
 * @author Denis Dyadyun <sysadm85@gmail.com>
 */
class GenerateKeywordsTask extends AbstractTask
{
    const TASK_NAME = 'generateKeywords';
    const API_LIMIT = 100;
    const MAX_REQUEST_COUNT = 100;
    const TYPE_ALL = 'all';
    const TYPE_ONLY_KEYWORDS = 'keywords';

    const RUN_OPTION_OVERWRITE = 'overwrite';//перезаписать все, кроме добавленных вручную
    const RUN_OPTION_OVERWRITE_ALL = 'overwrite-all'; //перезаписать все ключевые слова
    const RUN_OPTION_LEAVE = 'leave'; //не трогать сгенерированные объявления

    /**
     * @var VariationStrategyInterface
     */
    protected $variationGenerator;

    /**
     * @var FileLogger
     */
    protected $logger;

    /**
     * @inheritDoc
     */
    protected function init()
    {
        parent::init();
        $variationStrategyFactory = new VariationStrategyFactory();
        $this->variationGenerator = $variationStrategyFactory->factory($this->task->shop);
        $this->logger = $this->getLogger();
    }

    /**
     * @inheritDoc
     */
    public function execute($params = [])
    {
        $this->logger->log('Start keywords generate...');
        $context = $this->task->getContext();
        $productSearch = new ProductsSearch();

        $onlyActive = ArrayHelper::getValue($context, 'onlyActive');

        $hasTemplates = AdTemplate::find()->andWhere(['shop_id' => $this->task->shop_id])->exists();

        if (!$hasTemplates) {
            throw new TaskException('Отсутствуют шаблоны объявлений');
        }

        $dataProvider = $productSearch->search([
            'shopId' => $this->task->shop_id,
            'categoryId' => ArrayHelper::getValue($context, 'categoryId'),
            'brandId' => ArrayHelper::getValue($context, 'brandId'),
            'dateFrom' => ArrayHelper::getValue($context, 'dateFrom'),
            'dateTo' => ArrayHelper::getValue($context, 'dateTo'),
            'onlyActive' => is_null($onlyActive) ? true : $onlyActive,
            'title' => ArrayHelper::getValue($context, 'title'),
            'adTitle' => ArrayHelper::getValue($context, 'adTitle'),
            'isRequireVerification' => ArrayHelper::getValue($context, 'isRequireVerification'),
            'withoutAd' => ArrayHelper::getValue($context, 'withoutAd'),
            'priceFrom' => ArrayHelper::getValue($context, 'priceFrom'),
            'priceTo' => ArrayHelper::getValue($context, 'priceTo'),
            'isGenerateAd' => true,
        ]);


        $dataProvider->query->orderBy('ep.price ASC, ep.created_at ASC');

        if (ArrayHelper::getValue($context, 'runType') == 'partial') {
            $this->fillKeywordsToVariations($context);
        }

        $generationType = ArrayHelper::getValue($context, 'type', self::TYPE_ALL);

        $hasDeleted = false;
        $reqNumber = 0;

        if ($dataProvider->query instanceof Query) {
            $this->logger->log($dataProvider->query->createCommand()->getRawSql());
        } else {
            $this->logger->log(var_export($dataProvider->query, true));
        }

        $this->logger->log("Total count: " . $dataProvider->totalCount);

        $blackVariations = BlackList::find()
            ->select('name')
            ->andWhere([
                'shop_id' => $this->task->shop_id,
                'type' => BlackList::TYPE_KEYWORD
            ])
            ->column();

        foreach ($blackVariations as $blackVariation) {
            $this->variationGenerator->addVariation($blackVariation);
        }

        $pagination = $dataProvider->getPagination();
        $pagination->defaultPageSize = 500;
        $pagination->pageSizeLimit = [1, 500];
        $page = 0;

        while (true) {
            $reqNumber++;
            $this->logger->log("Request number: $reqNumber");

            $pagination->setPage($page);
            $dataProvider->prepare(true);
            $products = $dataProvider->getModels();
            $page++;

            $this->logger->log('Товаров получено:' . count($products));
            $this->logger->log('Ид товаров: "' . implode(', ', ArrayHelper::getColumn($products, 'id')) . '"');
            if (empty($products)) {
                break;
            }
            
            $productsModel = Product::find()
                ->with('ads')
                ->andWhere([
                    '{{%product}}.shop_id' => $this->task->shop_id,
                    '{{%product}}.product_id' => ArrayHelper::getColumn($products, 'id')
                ])
                ->indexBy('product_id')
                ->all();

            foreach ($products as $product) {
                $extProduct = ExtProduct::createFrom($product);

                if (array_key_exists($extProduct->id, $productsModel)) {
                    /** @var Product $productModel */
                    $productModel = $productsModel[$extProduct->id];
                    $productModel->brand_id = $extProduct->getBrandId();
                    $productModel->category_id = $extProduct->getCategoryId();
                    $productModel->title = $extProduct->title;
                    if (!$productModel->save()) {
                        throw new TaskException(
                            'Ошибка при сохранении товара - ' . ArrayHelper::first($productModel->getFirstErrors())
                        );
                    }
                } else {
                    $productModel = new Product([
                        'shop_id' => $this->task->shop_id,
                        'product_id' => (int)$extProduct->id,
                        'brand_id' => (int)$extProduct->getBrandId(),
                        'title' => $extProduct->title,
                        'price' => round($extProduct->price),
                        'is_available' => $extProduct->isAvailable,
                        'category_id' => $extProduct->getCategoryId()
                    ]);
                    $productModel->save();

                    if (!$productModel->primaryKey) {
                        $errors = $productModel->getFirstErrors();
                        $this->logger->log('Ошибка при создании товара: ' . implode(',', $errors));
                        $this->logger->log('Товар: ' .
                            json_encode($productModel->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                        );
                        continue;
                    }
                }

                $ads = $productModel->ads;
                $this->variationGenerator->addVariationsFromAds(array_filter($ads, function (Ad $ad) {
                    return !$ad->is_auto;
                }));
                $generatedInfo = $this->variationGenerator->generate($extProduct);
                $this->logger->log("Генерация для товара: {$productModel->id}, {$productModel->title}");
                $this->logger->log('Сгенерированных объявления: ' . json_encode($generatedInfo, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
                $updCount = 0;
                $deleteCount = 0;
                $hasKeywords = false;
                $runOption = ArrayHelper::getValue($context, 'runOption', self::RUN_OPTION_LEAVE);

                foreach ($ads as $ad) {
                    $hasChanges = false;
                    if ($ad->is_auto && isset($generatedInfo[$updCount])) {
                        if (in_array($runOption, [self::RUN_OPTION_OVERWRITE_ALL, self::RUN_OPTION_OVERWRITE])) {

                            $transaction = \Yii::$app->db->beginTransaction();

                            $newKeywords = $generatedInfo[$updCount]['keywords'];

                            /*
                             * удаление ключевых фраз, которых нет в сгенерированных и выбраны соответствующие
                             * опции генерации объявлений
                            */
                            foreach ($ad->adKeywords as $keyword) {
                                if (!in_array($keyword->keyword, $newKeywords) &&
                                    (
                                        $runOption == self::RUN_OPTION_OVERWRITE_ALL ||
                                        ($runOption== self::RUN_OPTION_OVERWRITE && $keyword->is_generated)
                                    )
                                ) {
                                    $keyword->delete();
                                    $hasChanges = true;
                                }
                            }

                            $ad->refresh();
                            $existsKeywords = $ad->adKeywords;

                            try {
                                $existsKeywords = array_map(
                                    'trim', ArrayHelper::getColumn($existsKeywords, 'keyword')
                                );

                                $delta = count($newKeywords) - count($existsKeywords);

                                foreach ($newKeywords as $keyword) {
                                    if (in_array($keyword, $existsKeywords)) {
                                        continue;
                                    }

                                    $keyword = new AdKeyword([
                                        'ad_id' => $ad->primaryKey,
                                        'keyword' => $keyword,
                                        'is_generated' => true
                                    ]);
                                    $hasChanges = true;
                                    $keyword->save();
                                }

                                if ($delta != 0) {
                                    $groups = [];

                                    foreach ($ad->yandexAds as $yandexAd) {
                                        $group = $yandexAd->adYandexGroup;

                                        if (!$group) {
                                            continue;
                                        }

                                        $groups[$group->primaryKey] = $group;
                                    }

                                    /**
                                     * @var AdYandexGroup $group
                                     */
                                    foreach ($groups as $group) {
                                        $group->keywords_count += $delta;
                                        $group->save();
                                    }
                                }

                                /** @var Template $template */
                                $template = $generatedInfo[$updCount]['template'];
                                if ($generationType == self::TYPE_ALL) {
                                    $ad->title = $template->title;
                                    $ad->save();
                                }
                                $ad->refresh();
                                if (count($ad->adKeywords) == 0 ||
                                    !$this->variationGenerator->adHasCurrentBrands($ad, $extProduct)
                                ) {
                                    $this->logger->log('Помечаем объявление на удаление: ' . JsonHelper::encodeModelPretty($ad));
                                    $ad->markForDelete();
                                    $hasDeleted = true;
                                    $deleteCount++;
                                } else {
                                    $ad->generated_at = date(Ad::DATETIME_FORMAT);
                                }
                                $this->logger->log('Обновление объявления:' . JsonHelper::encodeModelPretty($ad));
                                if ($hasChanges) {
                                    $ad->updated_at = date(Ad::DATETIME_FORMAT);
                                }
                                $ad->save();
                            } catch (\Exception $e) {
                                $transaction->rollBack();
                                $this->logger->log('Error occurred with ad:' . JsonHelper::encodeModelPretty($ad));
                            }

                            $transaction->commit();
                        }
                        $updCount++;
                    } elseif ($ad->is_auto || $runOption == self::RUN_OPTION_OVERWRITE_ALL) {
                        if (!$this->variationGenerator->adHasCurrentBrands($ad, $extProduct)) {
                            $this->logger->log('Помечаем объявление на удаление: ' . JsonHelper::encodeModelPretty($ad));
                            $ad->markForDelete();
                            $hasDeleted = true;
                            $deleteCount++;
                        }
                    }

                    if (!empty($ad->keywords) && !$ad->is_deleted) {
                        $hasKeywords = true;
                    }
                }

                $skipCount = 0;

                if ($updCount < count($generatedInfo)) {
                    for ($i = $updCount; $i < count($generatedInfo); $i++) {
                        if (!empty($generatedInfo[$i]['keywords'])) {
                            $hasError = false;
                            $transaction = \Yii::$app->db->beginTransaction();

                            try {
                                $hasKeywords = true;
                                /** @var Template $template */
                                $template = $generatedInfo[$i]['template'];
                                $ad = new Ad([
                                    'title' => $template->title,
                                    'keywords' => implode("\r\n", $generatedInfo[$i]['keywords']),
                                    'product_id' => $productModel->primaryKey,
                                    'is_auto' => true,
                                    'generated_at' => date('Y-m-d H:i:s'),
                                ]);
                                $ad->save();

                                foreach ($generatedInfo[$i]['keywords'] as $keyword) {
                                    try {
                                        $keyword = new AdKeyword([
                                            'ad_id' => $ad->primaryKey,
                                            'keyword' => trim($keyword),
                                            'is_generated' => true,
                                        ]);
                                        $keyword->save();
                                    } catch (\Exception $e) {
                                        // duplicated keyword
                                        throw $e;
                                    }
                                }
                            } catch (\Exception $e) {
                                $hasError = true;
                                $transaction->rollBack();
                                $skipCount++;
                                $this->logger->log('Error occurred with ad:' . JsonHelper::encodeModelPretty($ad));
                            }

                            if (!$hasError) {
                                $transaction->commit();
                                $this->logger->log('Создание нового объявления:' . JsonHelper::encodeModelPretty($ad));
                            }

                        } else {
                            $skipCount++;
                        }
                    }
                }

                if (!$hasKeywords) {
                    $productModel->is_duplicate = true;
                } else {
                    $productModel->is_duplicate = false;
                }
                $productModel->save();
            }
        }

        if ($hasDeleted && !TaskQueue::hasActiveTasks($this->task->shop_id, DeleteAdTask::TASK_NAME)) {
            TaskQueue::createNewTask($this->task->shop_id, DeleteAdTask::TASK_NAME);
        }
    }

    /**
     * Заполнение генератора вариаций существующими вариациями
     *
     * @param array $context
     */
    protected function fillKeywordsToVariations(array $context)
    {
        $productSearch = new ProductsSearch();

        $onlyActive = ArrayHelper::getValue($context, 'onlyActive');
        $dataProvider = $productSearch->search([
            'shopId' => $this->task->shop_id,
            'categoryId' => ArrayHelper::getValue($context, 'categoryId'),
            'brandId' => ArrayHelper::getValue($context, 'brandId'),
            'priceFrom' => ArrayHelper::getValue($context, 'priceFrom'),
            'priceTo' => ArrayHelper::getValue($context, 'priceTo'),
            'onlyActive' => is_null($onlyActive) ? true : $onlyActive,
            'isGenerateAd' => true
        ]);

        //будут обработаны только товары без объявлений
        $isWithoutAd = ArrayHelper::getValue($context, 'withoutAd');

        $pagination = $dataProvider->getPagination();
        $pagination->defaultPageSize = 500;
        $pagination->pageSizeLimit = [1, 500];
        $page = 0;

        $updateChecker = KeywordsUpdateChecker::createFromContext($context);
        $updateChecker->setLogger($this->logger);

        while (true) {
            $pagination->setPage($page);
            $dataProvider->prepare(true);
            $page++;

            $products = $dataProvider->getModels();

            if (empty($products)) {
                break;
            }

            $productsModel = Product::find()
                ->with('ads')
                ->andWhere([
                    '{{%product}}.shop_id' => $this->task->shop_id,
                    '{{%product}}.product_id' => ArrayHelper::getColumn($products, 'id')
                ])
                ->indexBy('product_id')
                ->all();

            foreach ($products as $product) {
                $extProduct = ExtProduct::createFrom($product);

                if (!array_key_exists($extProduct->id, $productsModel)) {
                    continue;
                }

                $productModel = $productsModel[$extProduct->id];
                $ads = $productModel->ads;

                foreach ($ads as $ad) {
                    if ($isWithoutAd || !$updateChecker->isNeedUpdate($extProduct, $ad)) {
                        $this->variationGenerator->addVariationsFromAds([$ad]);
                    }
                }
            }
        }
    }

    /**
     * @inheritDoc
     */
    protected function getLogFileName()
    {
        return "generate_keywords_{$this->task->id}";
    }
}
