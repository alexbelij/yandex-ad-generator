<?php

namespace app\controllers\generator;

use app\controllers\BaseController;
use app\helpers\TreeHelper;
use app\lib\api\shop\gateways\BrandsGateway;
use app\lib\api\shop\gateways\CategoriesGateway;
use app\lib\services\BrandCountService;
use app\lib\services\BrandService;
use app\lib\tasks\DeleteAdTask;
use app\models\Ad;
use app\models\AdKeyword;
use app\models\AdTemplate;
use app\models\AdYandexCampaign;
use app\models\AdYandexGroup;
use app\models\BlackList;
use app\models\GeneratorSettings;
use app\models\Product;
use app\models\search\ProductsSearch;
use app\models\Shop;
use app\models\TaskQueue;
use yii\base\InvalidParamException;
use app\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\ServerErrorHttpException;

/**
 * Class KeywordsController
 * @package app\controllers\generator
 */
class KeywordsController extends BaseController
{
    /**
     * @param int $shopId
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionIndex($shopId)
    {
        /** @var Shop $shop */
        $shop = Shop::findOne($shopId);

        if (!$shop) {
            throw new NotFoundHttpException();
        }

        /** @var BrandsGateway $brandsGateway */
        $brandsGateway = BrandsGateway::factory($shop);
        /** @var GeneratorSettings $generatorSettings */
        $generatorSettings = GeneratorSettings::find()->where(['shop_id' => $shopId])->one();
        $brandIds = !empty($generatorSettings->brands) ? explode(',', $generatorSettings->brands) : [];
        $brandService = new BrandService($brandsGateway);

        if (!empty($brandIds)) {
            $brands = $brandsGateway->getBrandsList($brandIds);
        } else {
            $brands = $brandService->getAvailableBrands($generatorSettings->categoryIds);
        }

        $searchModel = $this->getSearchModel($generatorSettings, $this->request->queryParams);

        $dataProvider = $searchModel->search();
        $dataProvider->query->orderBy('ep.price ASC');

       // echo $searchModel->search()->query->createCommand()->getRawSql();die;

        /** @var CategoriesGateway $categoriesGateway */
        $categoriesGateway = CategoriesGateway::factory($shop);

        $selectedCategoriesIds = ArrayHelper::getValue($this->request->get($searchModel->formName()), 'categoryId', []);
        if (!empty($selectedCategoriesIds)) {
            $selectedCategoriesIds = (array)$searchModel->categoryId;
        }

        $categoriesTree = TreeHelper::getCategoriesTree(
            $categoriesGateway->getList($generatorSettings->getCategoryIds()),
            array_filter((array)$selectedCategoriesIds)
        );

        return $this->render('index', [
            'brands' => $brands,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'brandCountService' => new BrandCountService($shop),
            'categoriesTree' => $categoriesTree,
        ]);
    }

    /**
     * @param GeneratorSettings $generatorSettings
     * @param array $params
     * @return ProductsSearch
     */
    protected function getSearchModel(GeneratorSettings $generatorSettings, array $params)
    {
        $searchModel = new ProductsSearch();
        $searchModel->shopId = $generatorSettings->shop_id;
        $searchModel->load($params);

        if (!empty($generatorSettings->price_from)) {
            $searchModel->priceFrom = $generatorSettings->price_from;
        }

        if (!empty($generatorSettings->price_to)) {
            $searchModel->priceTo = $generatorSettings->price_to;
        }

        $brandIds = !empty($generatorSettings->brands) ? explode(',', $generatorSettings->brands) : [];

        if (!empty($brandIds) && empty($searchModel->brandId)) {
            $searchModel->defaultBrandIds = $brandIds;
        }

        if (!empty($generatorSettings->getCategoryIds()) && empty($searchModel->categoryId)) {
            $searchModel->categoryId = $generatorSettings->getCategoryIds();
        }

        return $searchModel;
    }

    public function actionStub()
    {
        $this->response->format = Response::FORMAT_JSON;
        if ($this->request->post('hasEditable')) {
            $productInfo = $this->request->post('Products');
            $productInfo = reset($productInfo);
            $manualPrice = round($productInfo['manual_price']);
            $price = round($productInfo['price']);
            return ['output' => $manualPrice . ($price != $manualPrice ? ' (manual)' : '')];
        }

        return ['output' => ''];
    }

    /**
     * Сохранение информации о товарах
     *
     * @param int $shopId
     * @return array
     */
    public function actionSaveProducts($shopId)
    {
        $this->response->format = Response::FORMAT_JSON;
        $productsData = $this->request->post('Products', []);
        $adData = $this->request->post('Ad', []);
        unset($adData['new']);
        $productIds = array_keys($productsData);

        $newAds = $this->getNewAds();
        $ads = [];
        foreach ($adData as $adId => $adItem) {
            $ads[$adItem['product_id']][$adId] = $adItem;
        }

        /** @var Product[] $products */
        $products = Product::find()
            ->andWhere(['product_id' => $productIds, 'shop_id' => $shopId])
            ->indexBy('product_id')
            ->all();

        foreach ((array)$productsData as $productId => $item) {

            //товары без ключевых слов или заголовка не сохраняем
            if (empty($products[$productId]) &&
                empty($newAds[$productId])
            ) {
                continue;
            }

            $item['price'] = round(str_replace(',', '.', $item['price']));
            $item['manual_price'] = !empty($item['manual_price']) ?
                round(str_replace(',', '.', $item['manual_price'])) : null;

            if ($item['manual_price'] && $item['manual_price'] == $item['price']) {
                $item['manual_price'] = null;
            }

            if (isset($products[$productId])) {
                $product = $products[$productId];
                $product->attributes = $item;
                $product->save();
            } else {
                $product = new Product($item);
                $product->shop_id = $shopId;
                $product->product_id = $productId;
                $product->save();
            }

            $existsProductAds = ArrayHelper::index($product->ads, 'id');
            $receivedProductAds = ArrayHelper::getValue($ads, $product->primaryKey, []);

            $toDelete = array_diff_key($existsProductAds, $receivedProductAds);

            if (!empty($toDelete)) {
                Ad::updateAll(['is_deleted' => 1], ['id' => array_keys($toDelete)]);
                if (!TaskQueue::hasActiveTasks($shopId, DeleteAdTask::TASK_NAME)) {
                    TaskQueue::createNewTask($shopId, DeleteAdTask::TASK_NAME);
                }
            }

            //добавление новых объявлений
            if (!empty($newAds[$productId])) {
                foreach ($newAds[$productId] as $newAd) {
                    if (!empty($newAd['title']) && !empty($newAd['keywords'])) {
                        $ad = new Ad([
                            'title' => $newAd['title'],
                            'keywords' => $newAd['keywords'],
                            'product_id' => $product->primaryKey,
                            'is_require_verification' => !empty($newAd['is_require_verification'])
                        ]);
                        $ad->save();
                    }
                }
            }
        }

        return ['status' => 'success'];
    }

    /**
     * Возвращает массив новых объялений для добавления
     * @return array
     */
    protected function getNewAds()
    {
        $adData = $this->request->post('Ad');
        $rawNewAds = ArrayHelper::getValue($adData, 'new', []);

        $newAds = [];
        foreach ($rawNewAds as $productId => $ads) {
            $newAds[$productId] = [];
            for ($i = 0; $i < count($ads['title']); $i++) {
                $newAds[$productId][] = [
                    'title' => $ads['title'][$i],
                    'keywords' => $ads['keywords'][$i],
                    'is_require_verification' => !empty($ads['is_require_verification'][$i])
                ];
            }
        }

        return $newAds;
    }

    /**
     * Пометить как требует/не требует проверки
     *
     * @param int $shopId
     * @return array
     */
    public function actionMarkVerify($shopId)
    {
        $this->response->format = Response::FORMAT_JSON;

        /** @var GeneratorSettings $generatorSettings */
        $generatorSettings = GeneratorSettings::find()->andWhere(['shop_id' => $shopId])->one();

        if (!$generatorSettings) {
            throw new InvalidParamException('Generator settings not found');
        }

        $searchModel = $this->getSearchModel($generatorSettings, $this->request->post());
        $dataProvider = $searchModel->search();

        $pagination = $dataProvider->getPagination();
        $pagination->defaultPageSize = 100;
        $pagination->pageSizeLimit = [1, 100];
        $page = 0;
        $verify = (int)$this->request->post('verify');

        while (true) {
            $dataProvider->prepare(true);
            $products = $dataProvider->getModels();
            $pagination->setPage(++$page);

            if (empty($products)) {
                break;
            }

            $productIds = implode(',', array_map('intval', ArrayHelper::getColumn($products, 'id')));

            $sql = "UPDATE {{%ad}} ad
                INNER JOIN {{%product}} p ON p.id = ad.product_id
                SET ad.is_require_verification = $verify
                WHERE p.product_id IN ($productIds)";

            \Yii::$app->db->createCommand($sql)->execute();
        }

        return ['status' => 'success'];
    }

    /**
     * @param int $shopId
     * @throws NotFoundHttpException
     */
    public function actionExportToXls($shopId)
    {
        ini_set('max_execution_time', 0);
        $shop = Shop::findOne($shopId);

        if (!$shop) {
            throw new NotFoundHttpException('Магазин не найден');
        }

        /** @var GeneratorSettings $generatorSettings */
        $generatorSettings = GeneratorSettings::find()->andWhere(['shop_id' => $shopId])->one();

        if (!$generatorSettings) {
            throw new InvalidParamException('Generator settings not found');
        }

        $searchModel = $this->getSearchModel($generatorSettings, $this->request->queryParams);

        $dataProvider = $searchModel->search();

        $phpExcel = new \PHPExcel();

        $sheet = $phpExcel->setActiveSheetIndex(0);

        $sheet->setCellValue('A1', 'Название товара');
        $sheet->setCellValue('B1', 'Заголовок объявления');
        $sheet->setCellValue('C1', 'Категория');
        $sheet->setCellValue('D1', 'Бренд');
        $sheet->setCellValue('E1', 'Модель');
        $sheet->setCellValue('F1', 'Цена');

        $page = 0;
        $pagination = $dataProvider->pagination;
        $pagination->defaultPageSize = 1000;
        $pagination->pageSizeLimit = [1, 1000];
        $pagination->setPage($page);

        $row = 2;
        while ($items = $dataProvider->getModels()) {

            $productIds = ArrayHelper::getColumn($items, 'id');
            $items = ArrayHelper::index($items, 'id');

            /** @var Ad[] $ads */
            $ads = Ad::find()
                ->joinWith(['product.externalProduct.category', 'product.externalProduct.brand'])
                ->andWhere([
                    'product.product_id' => $productIds,
                    'product.shop_id' => $shopId
                ])
                ->all();

            $indexedAds = [];
            foreach ($ads as $ad) {
                $indexedAds[$ad->product->product_id][] = $ad;
            }

            foreach ($items as $item) {

                if (!empty($item['type_prefix'])) {
                    $catPrefix = $item['type_prefix'];
                } else {
                    $catPrefix = $item['categories'][0]['title'];
                }

                $productTitle = $catPrefix . ' ' . $item['brand']['title'] . ' ' . $item['title'];

                if (!empty($indexedAds[$item['id']])) {
                    /** @var Ad $ad */
                    foreach ($indexedAds[$item['id']] as $ad) {
                        $sheet->setCellValue("A{$row}", $productTitle);
                        $sheet->setCellValue("B{$row}", $ad->title);
                        $sheet->setCellValue("C{$row}", ArrayHelper::getValue($ad, 'product.externalProduct.category.title'));
                        $sheet->setCellValue("D{$row}", ArrayHelper::getValue($ad, 'product.externalProduct.brand.title'));
                        $sheet->setCellValue("E{$row}", ArrayHelper::getValue($ad, 'product.externalProduct.title'));
                        $sheet->setCellValue("F{$row}", ArrayHelper::getValue($ad, 'product.price'));
                        $row++;
                    }
                } else {
                    $sheet->setCellValue("A{$row}", $productTitle);
                    $sheet->setCellValue("B{$row}", '');
                    $sheet->setCellValue("C{$row}", ArrayHelper::getValue($item, 'categories.0.title'));
                    $sheet->setCellValue("D{$row}", ArrayHelper::getValue($item, 'brand.title'));
                    $sheet->setCellValue("E{$row}", ArrayHelper::getValue($item, 'title'));
                    $sheet->setCellValue("F{$row}", ArrayHelper::getValue($item, 'price'));
                    $row++;
                }
            }

            $pagination->setPage(++$page);
            $dataProvider->prepare(true);
        }

        $tmpFileName = tempnam('/tmp', '_xls');
        $objWriter = \PHPExcel_IOFactory::createWriter($phpExcel, 'Excel2007');
        $objWriter->save($tmpFileName);

        return \Yii::$app->response->sendFile($tmpFileName, 'ads.xlsx');
    }

    /**
     * @param $adId
     * @return array
     * @throws NotFoundHttpException
     */
    public function actionUpdateKeyword($adId)
    {
        $this->response->format = Response::FORMAT_JSON;
        $newKeyword = trim($this->request->post('keyword'));
        $oldKeyword = trim($this->request->post('old_keyword'));

        if ($newKeyword == $oldKeyword) {
            return [
                'output' => $newKeyword
            ];
        }
        $ad = Ad::findOne($adId);

        if (!$ad) {
            throw new NotFoundHttpException();
        }

        $keywords = $ad->getKeywordsList();
        foreach ($keywords as $i => $keyword) {
            if ($keyword == $oldKeyword) {
                $keywords[$i] = $newKeyword;
                break;
            }
        }
        $ad->keywords = implode("\r\n", $keywords);
        $ad->save();

        return ['output' => $newKeyword];
    }

    /**
     * @param $adId
     * @return array
     * @throws NotFoundHttpException
     */
    public function actionAddKeywords($adId)
    {
        $this->response->format = Response::FORMAT_JSON;
        $newKeywords = $this->request->post('newKeywords');
        $newKeywords = array_filter(array_map('trim', preg_split("#\r\n|\n#", $newKeywords)));

        if (empty($newKeywords)) {
            return ['message' => 'Нет ключевых слов для сохранения', 'output' => ''];
        }

        $ad = Ad::findOne($adId);

        if (!$ad) {
            throw new NotFoundHttpException();
        }

        $existsKeywords = $ad->getKeywordsList();
        $keywords = array_unique(array_merge($existsKeywords, $newKeywords));
        $ad->keywords = implode("\r\n", $keywords);
        $ad->save();

        $transaction = \Yii::$app->db->beginTransaction();

        try {
            // Create new keywords
            foreach (array_diff($newKeywords, $existsKeywords) as $keyword) {
                $keyword = new AdKeyword([
                    'ad_id' => $ad->primaryKey,
                    'keyword' => trim($keyword),
                ]);
                $keyword->save();
            }
        } catch (\Exception $e) {
            \Yii::getLogger()->log($e->getMessage());
            $transaction->rollBack();
        }

        $transaction->commit();

        return ['output' => 'Добавить фразы'];
    }

    /**
     * Удаление ключевого слова
     *
     * @throws NotFoundHttpException
     */
    public function actionDeleteKeyword()
    {
        $this->response->format = Response::FORMAT_JSON;
        $adId = $this->request->post('adId');
        $keywordToDelete = trim($this->request->post('keyword'));

        if (empty($keywordToDelete)) {
            return ['status' => 'error'];
        }

        /** @var Ad $ad */
        $ad = Ad::findOne($adId);

        if (!$ad) {
            throw new NotFoundHttpException();
        }

        $result = [];
        foreach ($ad->getKeywordsList() as $keyword) {
            if ($keyword != $keywordToDelete) {
                $result[] = $keyword;
            } else {
                $keyword = AdKeyword::findOne([
                    'ad_id' => $ad->primaryKey,
                    'keyword' => trim($keyword),
                ]);

                if ($keyword) {
                    $keyword->delete();
                }
            }
        }

        $ad->keywords = implode("\r\n", $result);

        if ($ad->save()) {
            $blackList = new BlackList([
                'shop_id' => $ad->product->shop_id,
                'name' => $keywordToDelete,
                'type' => BlackList::TYPE_KEYWORD
            ]);

            $blackList->save();
        }

        return ['status' => 'success'];
    }

    /**
     * @param int $adId
     * @return array
     * @throws NotFoundHttpException
     */
    public function actionUpdateTitle($adId)
    {
        $this->response->format = Response::FORMAT_JSON;
        $title = ArrayHelper::getValue($this->request->post('Ad'), 'title');
        $ad = Ad::findOne($adId);

        if (!$ad) {
            throw new NotFoundHttpException('Объявление не найдено');
        }

        if (mb_strlen($title) > AdTemplate::TITLE_MAX_SIZE) {
            return ['message' => 'Максимальная длина заголовка 33 символа'];
        }

        $ad->title = $title;
        $ad->save();

        return ['output' => $title];
    }

    /**
     * @param int $id
     * @return array
     * @throws NotFoundHttpException
     */
    public function actionRequireVerification($id)
    {
        $this->response->format = Response::FORMAT_JSON;
        $ad = Ad::findOne($id);

        if (!$ad) {
            throw new NotFoundHttpException('Объявление не найдено');
        }

        $ad->is_require_verification = $this->request->post('isRequire', 0);
        $ad->save();

        return ['status' => 'success'];
    }

    /**
     * @return array
     * @throws BadRequestHttpException
     * @throws ServerErrorHttpException
     */
    public function actionSaveNewAd()
    {
        $this->response->format = Response::FORMAT_JSON;

        $shopId = $this->request->post('product')['shop_id'];
        $productId = $this->request->post('productId');

        if (empty($shopId) || !$productId) {
            throw new BadRequestHttpException();
        }

        $product = Product::findOne([
            'product_id' => $productId,
            'shop_id' => $shopId
        ]);

        if (!$product) {
            $product = new Product($this->request->post('product'));
            $product->product_id = $this->request->post('productId');
            if (!$product->save()) {
                throw new ServerErrorHttpException(ArrayHelper::first($product->getFirstErrors()));
            }
        }

        $ad = new Ad();
        $ad->title = $this->request->post('title');
        $ad->keywords = $this->request->post('keywords');
        $ad->is_require_verification = $this->request->post('isRequireVerification');
        $ad->product_id = $product->primaryKey;

        if ($ad->save()) {
            return ['status' => 'success'];
        } else {
            return ['status' => 'error', 'message' => ArrayHelper::first($ad->getFirstErrors())];
        }
    }

    /**
     * @param int $adId
     * @return array
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     */
    public function actionRemoveAd($adId)
    {
        $this->response->format = Response::FORMAT_JSON;

        if (!$adId) {
            throw new BadRequestHttpException();
        }

        $ad = Ad::findOne($adId);

        if (!$ad) {
            throw new NotFoundHttpException();
        }
        $ad->markForDelete();

        return ['status' => 'success'];
    }

    /**
     * Установить статус мало показов для группы объявления
     *
     * @return array
     * @throws ServerErrorHttpException
     */
    public function actionShuffleGroups()
    {
        $this->response->format = Response::FORMAT_JSON;
        $yandexCampaignId = (int)$this->request->post('yandexCampaignId');
        $value = (int)$this->request->post('value');

        if (!$yandexCampaignId) {
            throw new ServerErrorHttpException('Yandex campaign id is blank!');
        }

        $yandexCampaign = AdYandexCampaign::findOne($yandexCampaignId);
        if (!$yandexCampaign) {
            throw new NotFoundHttpException();
        }

        $servingStatus = AdYandexGroup::SERVING_STATUS_ELIGIBLE;
        if ($value) {
            $servingStatus = AdYandexGroup::SERVING_STATUS_RARELY_SERVED;
        }

        AdYandexGroup::updateAll(['serving_status' => $servingStatus], ['id' => $yandexCampaign->ad_yandex_group_id]);

        return ['status' => 'ok'];
    }
}
