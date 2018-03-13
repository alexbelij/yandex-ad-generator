<?php

namespace app\controllers;

use app\lib\api\yandex\auth\BrandApiIdentity;
use app\lib\api\yandex\direct\Connection;
use app\lib\api\yandex\direct\resources\CampaignResource;
use app\lib\api\yandex\direct\resources\DictionaryResource;
use app\lib\services\DictionaryService;
use app\lib\services\YandexCampaignService;
use app\models\BrandAccount;
use app\models\forms\CampaignTemplateForm;
use app\models\Shop;
use Yii;
use app\models\CampaignTemplate;
use app\models\search\CampaignTemplateSearch;
use yii\caching\FileCache;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * CampaignTemplateController implements the CRUD actions for CampaignTemplate model.
 */
class CampaignTemplatesController extends BaseController
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @param int $shopId
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionIndex($shopId)
    {
        $shop = Shop::findOne($shopId);
        
        if (!$shop) {
            throw new NotFoundHttpException();
        }
        $searchModel = new CampaignTemplateSearch();
        $searchModel->shop_id = $shopId;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'dictionaryService' => $this->getDictionaryService($shopId)
        ]);
    }

    /**
     * @param int $shopId
     * @return DictionaryService
     */
    protected function getDictionaryService($shopId)
    {
        $shop = Shop::findOne($shopId);
        $connection = new Connection(new BrandApiIdentity($shop));
        return new DictionaryService(new DictionaryResource($connection));
    }

    /**
     * Creates a new CampaignTemplate model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate($shopId)
    {
        $model = new CampaignTemplateForm();
        $model->shop_id = $shopId;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index', 'shopId' => $shopId]);
        } else {
            return $this->render('create', [
                'model' => $model,
                'regions' => $this->getDictionaryService($shopId)->getGeoRegions()
            ]);
        }
    }

    /**
     * Updates an existing CampaignTemplate model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index', 'shopId' => $model->shop_id]);
        } else {
            return $this->render('update', [
                'model' => $model,
                'regions' => $this->getDictionaryService($model->shop_id)->getGeoRegions()
            ]);
        }
    }

    /**
     * @param $id
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        if (!$model) {
            throw new NotFoundHttpException();
        }

        $yandexCampaigns = $model->yandexCampaigns;
        foreach ($yandexCampaigns as $yandexCampaign) {
            if ($yandexCampaign->yandex_id) {
                $campaignResource = new CampaignResource(new Connection(new BrandApiIdentity($model->shop, $model->brand_id)));
                $campaignService = new YandexCampaignService($campaignResource);
                $campaignService->remove($yandexCampaign->yandex_id);
            }
        }

        $model->delete();

        return $this->redirect(['index', 'shopId' => $model->shop_id]);
    }

    /**
     * Finds the CampaignTemplate model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return CampaignTemplate the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = CampaignTemplateForm::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
