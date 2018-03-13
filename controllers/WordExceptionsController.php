<?php

namespace app\controllers;

use Yii;
use app\models\WordException;
use app\models\search\WordException as WordExceptionSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * WordExceptionsController implements the CRUD actions for WordException model.
 */
class WordExceptionsController extends BaseController
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
     * Lists all WordException models.
     * @return mixed
     */
    public function actionIndex($shopId)
    {
        $searchModel = new WordExceptionSearch();
        $searchModel->shop_id = $shopId;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new WordException model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate($shopId)
    {
        $model = new WordException();
        $model->shop_id = $shopId;

        if ($this->request->absoluteUrl != $this->request->referrer) {
            \Yii::$app->session->set('word_exceptions_referrer', $this->request->referrer);
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            if ($referrer = \Yii::$app->session->get('word_exceptions_referrer')) {
                return $this->redirect($referrer);
            }

            return $this->redirect(['index', 'shopId' => $model->shop_id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing WordException model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->absoluteUrl != $this->request->referrer) {
            \Yii::$app->session->set('word_exceptions_referrer', $this->request->referrer);
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {

            if ($referrer = \Yii::$app->session->get('word_exceptions_referrer')) {
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
     * Deletes an existing WordException model.
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
     * Finds the WordException model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return WordException the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = WordException::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
