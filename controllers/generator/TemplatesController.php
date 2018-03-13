<?php

namespace app\controllers\generator;

use app\controllers\BaseController;
use app\controllers\SiteController;
use app\lib\api\shop\gateways\BrandsGateway;
use app\lib\api\shop\gateways\CategoriesGateway;
use app\lib\services\BrandService;
use app\models\forms\AdTemplateForm;
use app\models\Shop;
use Yii;
use app\models\AdTemplate;
use app\models\search\TemplatesSearch;
use yii\db\ActiveRecord;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * TemplatesController implements the CRUD actions for Template model.
 */
class TemplatesController extends BaseController
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
        if (!$shopId) {
            throw new NotFoundHttpException();
        }
        $searchModel = new TemplatesSearch();
        $searchModel->shop_id = $shopId;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $dataProvider->sort = false;

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new Template model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @param int $shopId
     * @return mixed
     */
    public function actionCreate($shopId)
    {
        $model = new AdTemplateForm();

        $model->shop_id = (int)$shopId;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index', 'shopId' => $shopId]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Template model.
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
            ]);
        }
    }

    /**
     * Deletes an existing Template model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $model->delete();

        return $this->redirect(['index', 'shopId' => $model->shop_id]);
    }

    /**
     * Finds the Template model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return AdTemplate the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = AdTemplateForm::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
