<?php

namespace app\controllers;

use Yii;
use app\models\SitelinksItem;
use app\models\search\SitelinksItemSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * SitelinksItemController implements the CRUD actions for SitelinksItem model.
 */
class SitelinksItemController extends BaseController
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
     * Lists all SitelinksItem models.
     * @return mixed
     */
    public function actionIndex($sitelinkId)
    {
        $searchModel = new SitelinksItemSearch();
        $searchModel->sitelink_id = $sitelinkId;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'sitelinkId' => $sitelinkId
        ]);
    }

    /**
     * Creates a new SitelinksItem model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate($sitelinkId)
    {
        $model = new SitelinksItem();
        $model->sitelink_id = $sitelinkId;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index', 'sitelinkId' => $model->sitelink_id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing SitelinksItem model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index', 'sitelinkId' => $model->sitelink_id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing SitelinksItem model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $sitelinkId = $model->sitelink_id;
        $model->delete();

        return $this->redirect(['index', 'sitelinkId' => $sitelinkId]);
    }

    /**
     * Finds the SitelinksItem model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return SitelinksItem the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = SitelinksItem::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
