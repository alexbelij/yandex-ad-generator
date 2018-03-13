<?php

namespace app\modules\feed\controllers;

use app\controllers\BaseController;
use app\helpers\ArrayHelper;
use app\modules\feed\models\FeedSettings;
use Yii;
use app\modules\feed\models\FeedItem;
use app\modules\feed\models\search\FeedItemSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * FeedItemsController implements the CRUD actions for FeedItem model.
 */
class FeedItemsController extends BaseController
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all FeedItem models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new FeedItemSearch();

        $data = $this->request->get('FeedItemSearch');
        $settings = [];
        if ($this->request->isGet && isset($data['feed_id'])) {
            $feedSettings = FeedSettings::findOne(['feed_id' => $data['feed_id']]);
            if ($feedSettings) {
                $settings = json_decode($feedSettings->settings, true);
            }
        }

        $postData = $this->request->post();
        $onlySearch = $this->request->post('only_search');
        if ($postData && isset($data['feed_id']) && !$onlySearch) {
            $feedSettings = FeedSettings::findOne(['feed_id' => $data['feed_id']]);
            if (!$feedSettings) {
                $feedSettings = new FeedSettings([
                    'feed_id' => (int)$data['feed_id'],
                ]);
            }

            $feedSettings->settings = json_encode(['FeedItemSearch' => $postData['FeedItemSearch']]);
            $feedSettings->save();
        }

        $dataProvider = $searchModel->search(
            ArrayHelper::merge($settings, Yii::$app->request->queryParams, $postData)
        );

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single FeedItem model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new FeedItem model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new FeedItem();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing FeedItem model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing FeedItem model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the FeedItem model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return FeedItem the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = FeedItem::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     * @param int $id
     * @return array
     */
    public function actionActivate($id)
    {
        $this->response->format = Response::FORMAT_JSON;
        $feedItem = $this->findModel($id);

        $feedItem->is_active = $this->request->post('active');

        if ($feedItem->save()) {
            return [
                'status' => 'ok'
            ];
        }

        return [
            'error' => ArrayHelper::first($feedItem->getFirstErrors())
        ];
    }
}
