<?php

namespace app\controllers;

use Yii;
use app\models\ExternalCategory;
use app\models\search\ExtrernalCategorySearch;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * ExternalCategoryController implements the CRUD actions for ExternalCategory model.
 */
class ExternalCategoryController extends BaseController
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
     * Lists all ExternalCategory models.
     * @return mixed
     */
    public function actionIndex($shopId)
    {
        $searchModel = new ExtrernalCategorySearch();
        $searchModel->shop_id = $shopId;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new ExternalCategory model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate($shopId)
    {
        $model = new ExternalCategory();
        $model->shop_id = $shopId;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index', 'shopId' => $model->shop_id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing ExternalCategory model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        if ($this->request->absoluteUrl != $this->request->referrer) {
            \Yii::$app->session->set('external_category_referrer', $this->request->referrer);
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            if ($referrer = \Yii::$app->session->get('external_category_referrer')) {
                return $this->redirect($referrer);
            }
            return $this->redirect(['index', 'shopId' => $model->shop_id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing ExternalCategory model.
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
     * Finds the ExternalCategory model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return ExternalCategory the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = ExternalCategory::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
