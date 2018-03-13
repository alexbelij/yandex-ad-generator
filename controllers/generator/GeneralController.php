<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 12.03.16
 * Time: 19:00
 */

namespace app\controllers\generator;

use app\helpers\AccountHelper;
use app\helpers\ArrayHelper;
use app\lib\api\shop\gateways\BrandsGateway;
use app\controllers\BaseController;
use app\lib\api\shop\gateways\CategoriesGateway;
use app\lib\dto\Brand;
use app\lib\services\BrandCountService;
use app\lib\services\BrandService;
use app\lib\tasks\AdDuplicateDeleteTask;
use app\lib\tasks\AdSyncTask;
use app\lib\tasks\CampaignsUpdateTask;
use app\lib\tasks\DeleteAdTask;
use app\lib\tasks\DeleteAdWithoutVariationTask;
use app\lib\tasks\DownloadFileTask;
use app\lib\tasks\GenerateKeywordsTask;
use app\lib\tasks\ImportFileTask;
use app\lib\tasks\KeywordsSyncTask;
use app\lib\tasks\MinusKeywordsTask;
use app\lib\tasks\UpdateProductsTask;
use app\lib\tasks\YandexUpdateTask;
use app\lib\UploadedFile;
use app\models\Account;
use app\models\AdYandexCampaign;
use app\models\FileImport;
use app\models\Forecast;
use app\models\forms\GeneratorSettingsForm;
use app\models\GeneratorSettings;
use app\models\Shop;
use app\models\TaskQueue;
use yii\helpers\Url;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Class GeneralController
 * @package app\controllers\generator
 */
class GeneralController extends BaseController
{
    /**
     * @param $shopId
     * @return string|Response
     * @throws BadRequestHttpException
     */
    public function actionIndex($shopId)
    {
        /** @var Shop $shop */
        $shop = Shop::findOne($shopId);
        if (!$shop) {
            throw new BadRequestHttpException('Shop not found.');
        }

        $model = GeneratorSettingsForm::find()->andWhere(['shop_id' => $shopId])->one();
        if (!$model) {
            $model = new GeneratorSettingsForm([
                'shop_id' => $shopId
            ]);
        }

        $model
            ->setAvailableBrands($this->getAvailableBrands($model))
            ->setAvailableCategories($this->getCachedCategories($shop));

        $postData = $this->request->post();

        if ($model->load($postData) && $model->save()) {
            return $this->redirect(Url::current());
        }
        
        $lastUpdateAdTask = TaskQueue::getLastRunnedFor($shopId, YandexUpdateTask::TASK_NAME);
        $lastCampaignUpdateTask = TaskQueue::getLastRunnedFor($shopId, CampaignsUpdateTask::TASK_NAME);

        return $this->render('index', [
            'model' => $model,
            'lastTask' => $lastUpdateAdTask,
            'lastCampaignUpdateTask' => $lastCampaignUpdateTask,
            'brandCountService' => new BrandCountService($shop)
        ]);
    }

    /**
     * @param Shop $shop
     * @return array|mixed
     */
    protected function getCachedCategories(Shop $shop)
    {
        $cacheKey = __METHOD__ . $shop->id;

        if (false == ($categories = \Yii::$app->cache->get($cacheKey))) {
            /** @var CategoriesGateway $categoriesGateway */
            $categoriesGateway = CategoriesGateway::factory($shop);
            $categories = $categoriesGateway->getList();
            \Yii::$app->cache->set($cacheKey, $categories, 3600);
        }

        return $categories;
    }

    /**
     * @param GeneratorSettings $model
     * @return array|mixed
     */
    protected function getAvailableBrands(GeneratorSettings $model)
    {
        /** @var BrandsGateway $brandsApiGateway */
        $brandsApiGateway = BrandsGateway::factory($model->shop);
        $brandService = new BrandService($brandsApiGateway);

        return $brandService->getAvailableBrands($model->categoryIds);
    }

    /**
     * @param int $shopId
     * @return array
     * @throws BadRequestHttpException
     */
    public function actionSaveSettings($shopId)
    {
        $this->response->format = Response::FORMAT_JSON;

        $shop = Shop::findOne($shopId);

        if (!$shop) {
            throw new BadRequestHttpException();
        }

        $model = GeneratorSettingsForm::find()->andWhere(['shop_id' => $shopId])->one();
        if (!$model) {
            $model = new GeneratorSettingsForm([
                'shop_id' => $shopId
            ]);
        }

        $model
            ->setAvailableBrands($this->getAvailableBrands($model))
            ->setAvailableCategories($this->getCachedCategories($shop));

        $postData = $this->request->post();

        if (!isset($postData['categoryIds'])) {
            $postData['categoryIds'] = [];
        }

        if (!isset($postData['brandsList'])) {
            $postData['brandsList'] = [];
        }

        if ($model->load($postData, '') && $model->save()) {
            return ['status' => 'success'];
        }

        return ['status' => 'error', 'error' => reset($model->getFirstErrors())];
    }

    /**
     * Добавление задачи на обновление
     * @return array
     */
    public function actionStartUpdate()
    {
        $this->response->format = Response::FORMAT_JSON;

        $brandIds = array_values(array_filter(array_map('intval', $this->request->post('brandIds', []))));
        $categoryIds = array_values(array_filter(array_map('intval', $this->request->post('categoryIds', []))));
        $shopId = $this->request->post('shopId');
        $accountIds = $this->request->post('accountIds');

        if (empty($brandIds)) {
            return ['message' => 'Выберите хотя бы один бренд'];
        }

        $shop = Shop::findOne($shopId);

        if (empty($accountIds)) {
            $accountIds = AccountHelper::getAccountIds($shop, $brandIds);
        }

        /** @var Account[] $accounts */
        $accounts = Account::find()->andWhere(['id' => $accountIds])->all();

        $context = [
            'brandIds' => $brandIds,
            'shopId' => $shopId,
            'priceFrom' => (float) $this->request->post('priceFrom'),
            'priceTo' => (float) $this->request->post('priceTo'),
            'userId' => \Yii::$app->user->getId(),
            'categoryIds' => $categoryIds,
        ];

        if (!TaskQueue::hasActiveTasks($shopId, DeleteAdTask::TASK_NAME)) {
            TaskQueue::createNewTask($shopId, DeleteAdTask::TASK_NAME);
        }

        $messages = [];
        foreach ($accounts as $account) {
            $context['accountId'] = $account->id;
            if (TaskQueue::hasActiveTasks($shopId, YandexUpdateTask::TASK_NAME, ['accountId' => $account->id])) {
                $messages[] = 'Задача для аккаунта "' . $account->title . '" уже есть';
                continue;
            }

            $task = TaskQueue::createNewTask(
                $shopId,
                YandexUpdateTask::TASK_NAME,
                $context,
                "Аккаунт - {$account->title}, id - {$account->id}",
                ['accountId']
            );

            if ($task->hasErrors()) {
                $messages[] = 'Возникли ошибки: ' . reset($task->getFirstErrors());
                break;
            }
        }

        return [
            'message' => !empty($messages) ?
                implode("<br />\r\n", $messages) : 'Задача на обновление успешно создана',
        ];
    }
    
    public function actionStartCampaignsUpdate()
    {
        $this->response->format = Response::FORMAT_JSON;

        $shopId = $this->request->post('shopId');

        $task = TaskQueue::createNewTask($shopId, CampaignsUpdateTask::TASK_NAME, []);

        if ($task->hasErrors()) {
            $message = 'Возникли ошибки: ' . reset($task->getFirstErrors());
        } else {
            $message = 'Задача на обновление успешно создана';
        }

        return [
            'message' => $message
        ];
    }

    /**
     * Метод создает задачу на генерацию ключевых слов и заголовков
     *
     * @return array
     * @throws NotFoundHttpException
     */
    public function actionGenerateKeywords()
    {
        $this->response->format = Response::FORMAT_JSON;

        $shopId = $this->request->post('shopId');

        if (!$shopId) {
            throw new NotFoundHttpException();
        }

        /** @var GeneratorSettings $generatorSettings */
        $generatorSettings = GeneratorSettings::find()->andWhere(['shop_id' => $shopId])->one();

        if (!$generatorSettings) {
            throw new NotFoundHttpException();
        }

        $defaultBrandIds = !empty($generatorSettings->brands) ?
            explode(',', $generatorSettings->brands) : [];

        $brandId = $this->request->post('brandId');

        if (empty($brandId)) {
            $brandId = $defaultBrandIds;
        }

        $runOption = $this->request->post('runOption');
        $priceFrom = $this->request->post('priceFrom', $generatorSettings->price_from);
        $priceTo = $this->request->post('priceTo', $generatorSettings->price_to);
        $categoryId = $this->request->post('categoryIds', $generatorSettings->categoryIds);
        $dateFrom = $this->request->post('dateFrom');
        $dateTo = $this->request->post('dateTo');
        $adTitle = $this->request->post('adTitle');
        $title = $this->request->post('title');
        $onlyActive = $this->request->post('onlyActive');
        $isRequireVerification = $this->request->post('isRequireVerification');
        $withoutAd = $this->request->post('withoutAd');
        $type = $this->request->post('type', GenerateKeywordsTask::TYPE_ALL);
        $runType = $this->request->post('runType');

        if ($dateFrom && $dateTo) {
            if (strtotime($dateFrom) > strtotime($dateTo)) {
                return [
                    'status' => 'error',
                    'message' => 'Даты указаны неверно'
                ];
            }
        }

        $context = [
            'brandId' => $brandId,
            'runOption' => $runOption,
            'priceFrom' => $priceFrom,
            'priceTo' => $priceTo,
            'type' => $type,
            'categoryId' => $categoryId,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'adTitle' => $adTitle,
            'title' => $title,
            'onlyActive' => $onlyActive,
            'isRequireVerification' => $isRequireVerification,
            'withoutAd' => $withoutAd,
            'runType' => $runType,
        ];
        
        if (TaskQueue::hasActiveTasks($shopId, GenerateKeywordsTask::TASK_NAME, $context)) {
            return [
                'status' => 'error',
                'message' => 'Уже есть подобная задача на выполнение'
            ];
        } else {
            $task = TaskQueue::createNewTask($shopId, GenerateKeywordsTask::TASK_NAME, $context);
            if ($task) {
                return [
                    'status' => 'success',
                    'message' => 'Задача добавлена в очередь'
                ];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'Произошла ошибка при добавлении задачи, попробуйте добавить задачу позже'
                ];
            }
        }
    }

    /**
     * @return array
     * @throws BadRequestHttpException
     */
    public function actionUploadFile()
    {
        $this->response->format = Response::FORMAT_JSON;
        /** @var UploadedFile $file */
        $file = UploadedFile::getInstanceByName('uploadFile');

        $shopId = $this->request->post('shopId');

        if (!$shopId) {
            throw new BadRequestHttpException('shopId is required');
        }

        $shop = Shop::findOne($shopId);

        if (!$shop) {
            throw new BadRequestHttpException('Wrong shopId');
        }

        $fileUpload = new FileImport();
        $fileUpload->original_filename = $file->baseName;
        $fileUpload->size = $file->size;
        $fileUpload->type = $file->type;
        $fileUpload->shop_id = (int)$shopId;

        if ($file->extension) {
            $fileUpload->original_filename .= '.' . $file->extension;
        }

        if ($file->getHasError()) {
            $fileUpload->error_msg = $file->getErrorMessage();
            $fileUpload->save();
            throw new BadRequestHttpException($file->getErrorMessage(), $file->error);
        }

        $uploadDir = \Yii::getAlias('@app') . '/' . \Yii::$app->params['uploadDir'];
        $destFileName = $uploadDir . '/' . uniqid() . '.' . $file->extension;
        if (move_uploaded_file($file->tempName, $destFileName)) {

            $fileUpload->filename = $destFileName;

            \Yii::$app->db->close();
            \Yii::$app->db->open();

            if (TaskQueue::hasActiveTasks($shopId, ImportFileTask::TASK_NAME)) {
                unlink($destFileName);
                return ['error' => 'Подождите загрузки предыдущего файла'];
            }

            $fileUpload->save();
            TaskQueue::createNewTask($shopId, ImportFileTask::TASK_NAME, ['file_id' => $fileUpload->primaryKey]);
            return ['error' => ''];
        } else {
            return ['error' => error_get_last()];
        }
    }

    public function actionManualImport()
    {
        $this->response->format = Response::FORMAT_JSON;

        $shopId = $this->request->post('shopId');

        if (!$shopId) {
            throw new BadRequestHttpException('Параметр shopId не передан');
        }

        $shop = Shop::findOne($shopId);

        if (!$shop) {
            throw new BadRequestHttpException('Магазин не найден');
        }

        if (!TaskQueue::hasActiveTasks($shopId, DownloadFileTask::TASK_NAME)) {
            TaskQueue::createNewTask($shopId, DownloadFileTask::TASK_NAME);
            return [
                'message' => 'Задача на импорт создана'
            ];
        }

        return [
            'message' => 'Подобная задача уже есть'
        ];
    }

    /**
     * @return array
     */
    public function actionDeleteDuplicates()
    {
        $this->response->format = Response::FORMAT_JSON;
        $shopId = $this->request->post('shopId');

        if (!$shopId) {
            return [
                'status' => 'error',
                'msg' => 'Не передан параметр shopId'
            ];
        }

        TaskQueue::createNewTask($shopId, AdDuplicateDeleteTask::TASK_NAME);

        return ['status' => 'success'];
    }

    /**
     * @return array
     */
    public function actionDeleteAdWithoutBrand()
    {
        $this->response->format = Response::FORMAT_JSON;
        $shopId = $this->request->post('shopId');

        if (!$shopId) {
            return [
                'status' => 'error',
                'msg' => 'Не передан параметр shopId'
            ];
        }

        if (TaskQueue::hasActiveTasks($shopId, DeleteAdWithoutVariationTask::TASK_NAME)) {
            return ['status' => 'error', 'msg' => 'Задача уже создана'];
        }

        TaskQueue::createNewTask($shopId, DeleteAdWithoutVariationTask::TASK_NAME);

        return ['status' => 'success'];
    }

    /**
     * Запуск задачи на обновление товаров магазина
     *
     * @return array
     */
    public function actionStartUpdateProducts($shopId)
    {
        $this->response->format = Response::FORMAT_JSON;
        $brandIds = $this->request->post('brandIds');
        $priceFrom = $this->request->post('priceFrom');
        $priceTo = $this->request->post('priceTo');
        $categoryId = $this->request->post('categoryIds');

        if (!$shopId) {
            return [
                'status' => 'error',
                'msg' => 'Не передан параметр shopId'
            ];
        }

        if (TaskQueue::hasActiveTasks($shopId, UpdateProductsTask::TASK_NAME)) {
            return ['status' => 'err', 'msg' => 'Задача уже создана'];
        }

        $context = [
            'brandIds' => $brandIds,
            'priceFrom' => $priceFrom,
            'priceTo' => $priceTo,
            'categoryIds' => $categoryId
        ];
        if (TaskQueue::createNewTask($shopId, UpdateProductsTask::TASK_NAME, $context)) {
            return [
                'status' => 'success',
                'msg' => 'Обновление будет скоро запущено'
            ];
        }

        return [
            'status' => 'error',
            'msg' => 'Ошибка при создании задачи'
        ];
    }

    /**
     * Запуск минусации ключевых фраз
     *
     * @param $shopId
     * @return array
     */
    public function actionStartMinusKeywords($shopId)
    {
        $this->response->format = Response::FORMAT_JSON;

        if (!TaskQueue::hasActiveTasks($shopId, MinusKeywordsTask::TASK_NAME)) {
            TaskQueue::createNewTask($shopId, MinusKeywordsTask::TASK_NAME);
            return ['message' => 'Задача успешно добавлена'];
        }

        return ['message' => 'Задача на минусацию фраз уже существует'];
    }

    /**
     * Запуск минусации ключевых фраз
     *
     * @param $shopId
     * @return array
     */
    public function actionStartSyncAds($shopId)
    {
        $this->response->format = Response::FORMAT_JSON;

        if (!TaskQueue::hasActiveTasks($shopId, AdSyncTask::TASK_NAME)) {
            TaskQueue::createNewTask($shopId, AdSyncTask::TASK_NAME);
            return ['message' => 'Задача успешно добавлена'];
        }

        return ['message' => 'Задача на синхронизацию объявлений уже существует'];
    }

    /**
     * Запуск минусации ключевых фраз
     *
     * @param $shopId
     * @return array
     */
    public function actionStartSyncKeywords($shopId)
    {
        $this->response->format = Response::FORMAT_JSON;

        if (!TaskQueue::hasActiveTasks($shopId, KeywordsSyncTask::TASK_NAME)) {
            TaskQueue::createNewTask($shopId, KeywordsSyncTask::TASK_NAME);
            return ['message' => 'Задача успешно добавлена'];
        }

        return ['message' => 'Задача на синхронизацию ключевых фраз уже существует'];
    }

    /**
     *
     * Форма управления обновлением товаров
     *
     * @param int $shopId
     * @return array
     * @throws BadRequestHttpException
     */
    public function actionUpdateForm($shopId)
    {
        $this->response->format = Response::FORMAT_JSON;
        $shop = Shop::findOne($shopId);

        if (!$shop) {
            throw new BadRequestHttpException('Shop not found');
        }

        $brandIds = $this->request->get('brandIds');

        /** @var BrandsGateway $brandsApiGateway */
        $brandsApiGateway = BrandsGateway::factory($shop);

        $brands = $brandsApiGateway->getBrandsList($brandIds);
        $forecast = Forecast::find()
            ->andFilterWhere([
                'shop_id' => $shopId,
                'brand_id' => $brandIds
            ])
            ->indexBy('brand_id')
            ->all();

        $items = [];
        foreach ($brands as $brand) {
            $items[] = new Brand([
                'id' => $brand['id'],
                'title' => $brand['title'],
                'points' => ArrayHelper::getValue($forecast, "{$brand['id']}.points")
            ]);
        }

        /** @var Account[] $accounts */
        $accounts = ArrayHelper::index($shop->getAccounts($brandIds), 'id');
        $accountBrands = AccountHelper::getBrandsByAccount($shop, $brandIds);
        $brandAccountsMap = [];

        foreach ($accountBrands as $accountId => $accountBrandsList) {
            foreach ($accountBrandsList as $accountBrand) {
                $brandAccountsMap[$accountBrand['id']] = $accounts[$accountId]->title;
            }
        }

        return [
            'html' => $this->renderAjax('_updateForm', [
                'brands' => $items,
                'shop' => $shop,
                'accounts' => $accounts,
                'brandsByAccount' => $accountBrands,
                'brandAccountsMap' => $brandAccountsMap
            ])
        ];
    }

    /**
     * @throws BadRequestHttpException
     */
    public function actionDeleteAutoAds()
    {
        $this->response->format = Response::FORMAT_JSON;
        $shopId = $this->request->post('shopId');
        $brandIds = implode(',', array_map('intval', (array)$this->request->post('brandIds')));

        if (empty($brandIds) || !$shopId) {
            throw new BadRequestHttpException();
        }

        $sql = "
            UPDATE {{%ad}} a
            INNER JOIN {{%product}} p ON p.id = a.product_id
            SET is_deleted = 1
            WHERE a.is_auto = 1 AND p.brand_id IN ($brandIds) AND p.shop_id = :shopId
        ";

        $count = \Yii::$app->db->createCommand($sql, [':shopId' => $shopId])->execute();

        if (!TaskQueue::hasActiveTasks($shopId, DeleteAdTask::TASK_NAME) && $count > 0) {
            TaskQueue::createNewTask($shopId, DeleteAdTask::TASK_NAME);
        }

        return [
            'message' => $count > 0 ?
                "Помечено на удаление {$count} объявлений" : "Нет объявлений для удаления"
        ];
    }

    /**
     * Скачивание отчета об отклоненных объявлениях
     *
     * @param null $shopId
     */
    public function actionDownloadReportAds($shopId = null)
    {
        $phpExcel = new \PHPExcel();

        $sheet = $phpExcel->setActiveSheetIndex(0);

        $sheet->setCellValue('A1', 'Номер кампании');
        $sheet->setCellValue('B1', 'Название кампании');
        $sheet->setCellValue('C1', 'Номер группы объявлений');
        $sheet->setCellValue('D1', 'Название группы объявлений');

        $query= AdYandexCampaign::find()
            ->joinWith(['ad', 'ad.product', 'yandexCampaign'])
            ->andFilterWhere(['product.shop_id' => $shopId])
            ->andWhere(['ad_yandex_campaign.status' => AdYandexCampaign::STATUS_REJECTED]);

        $row = 2;
        foreach ($query->batch(1000) as $yandexAdCampaigns) {
            /** @var AdYandexCampaign $yandexAdCampaign */
            foreach ($yandexAdCampaigns as $yandexAdCampaign) {
                $sheet->setCellValue("A{$row}", $yandexAdCampaign->yandexCampaign->yandex_id);
                $sheet->setCellValue("B{$row}", $yandexAdCampaign->yandexCampaign->title);
                $sheet->setCellValue("C{$row}", $yandexAdCampaign->yandex_adgroup_id);
                $sheet->setCellValue("D{$row}", $yandexAdCampaign->yandex_group_name ?: $yandexAdCampaign->ad->title);
                $row++;
            }
        }

        $fileName = 'rejected_ads';
        if ($shopId) {
            $shop = Shop::findOne($shopId);
            $fileName = $shop->name . '_' . $fileName;
        }

        $fileName .= '_' . date('d_m_Y_H_i_s') . '.xls';

        header('Content-Type: application/vnd.ms-excel');
        header("Content-Disposition: attachment;filename='$fileName'");
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0
        $objWriter = \PHPExcel_IOFactory::createWriter($phpExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }

    /**
     * Формирование отчета по товарам, ссылки на объявления которых недоступны
     *
     * @param int $shopId
     */
    public function actionDownloadReportProductLinkFails($shopId)
    {
        $phpExcel = new \PHPExcel();

        $sheet = $phpExcel->setActiveSheetIndex(0);

        $sheet->setCellValue('A1', 'Номер кампании');
        $sheet->setCellValue('B1', 'Название кампании');
        $sheet->setCellValue('C1', 'Номер группы объявлений');
        $sheet->setCellValue('D1', 'Название группы объявлений');
        $sheet->setCellValue('E1', 'Название товара');
        $sheet->setCellValue('F1', 'Ссылка');

        $query = AdYandexCampaign::find()
            ->joinWith(['ad', 'ad.product', 'yandexCampaign', 'ad.product.externalProduct'])
            ->andFilterWhere(['product.shop_id' => $shopId])
            ->andWhere(['external_product.is_url_available' => 0]);

        $row = 2;
        foreach ($query->batch(1000) as $yandexAdCampaigns) {
            /** @var AdYandexCampaign $yandexAdCampaign */
            foreach ($yandexAdCampaigns as $yandexAdCampaign) {
                $sheet->setCellValue("A{$row}", $yandexAdCampaign->yandexCampaign->yandex_id);
                $sheet->setCellValue("B{$row}", $yandexAdCampaign->yandexCampaign->title);
                $sheet->setCellValue("C{$row}", $yandexAdCampaign->yandex_adgroup_id);
                $sheet->setCellValue("D{$row}", $yandexAdCampaign->yandex_group_name ?: $yandexAdCampaign->ad->title);
                $sheet->setCellValue("E{$row}", $yandexAdCampaign->ad->product->externalProduct->title);
                $sheet->setCellValue("F{$row}", $yandexAdCampaign->ad->product->externalProduct->url);
                $row++;
            }
        }

        $fileName = 'rejected_ads';
        if ($shopId) {
            $shop = Shop::findOne($shopId);
            $fileName = $shop->name . '_' . $fileName;
        }

        $fileName .= '_' . date('d_m_Y_H_i_s') . '.xls';

        header('Content-Type: application/vnd.ms-excel');
        header("Content-Disposition: attachment;filename='$fileName'");
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0
        $objWriter = \PHPExcel_IOFactory::createWriter($phpExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }
}
