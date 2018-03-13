<?php

namespace app\controllers;

use app\lib\api\yandex\auth\BrandApiIdentity;
use app\lib\api\yandex\direct\Connection;
use app\lib\api\yandex\direct\resources\CampaignResource;
use app\lib\services\YandexCampaignService;
use app\models\forms\CampaignForm;
use Yii;
use app\models\YandexCampaign;
use app\models\search\YandexCampaignSearch;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * CampaignsController implements the CRUD actions for YandexCampaign model.
 */
class CampaignsController extends BaseController
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
     * Lists all YandexCampaign models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new YandexCampaignSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Updates an existing YandexCampaign model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        $resource = new CampaignResource(new Connection(new BrandApiIdentity($model->shop, $model->brand_id)));

        $model->setCampaignService(new YandexCampaignService($resource));

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index']);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Обновление информации о кампании в апи
     *
     * @param YandexCampaign $model
     */
    protected function updateYandexCampaign(YandexCampaign $model)
    {
        $connection = new Connection(new BrandApiIdentity($model->shop, $model->brand_id));
        $resource = new CampaignResource($connection);
        $yandexCampaignService = new YandexCampaignService($resource);
        
        $yandexCampaignService->updateNegativeKeywords($model->yandex_id, $model->negative_keywords);
    }

    /**
     * @param $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {
        $campaign = $this->findModel($id);

        if (!$campaign) {
            throw new NotFoundHttpException();
        }

        if ($campaign->yandex_id) {
            $campaignResource = new CampaignResource(
                new Connection(new BrandApiIdentity($campaign->shop, $campaign->brand_id))
            );
            $campaignService = new YandexCampaignService($campaignResource);
            $campaignService->remove($campaign->yandex_id);
        }

        $campaign->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the YandexCampaign model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return CampaignForm
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = CampaignForm::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
