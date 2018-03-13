<?php

namespace app\modules\feed\controllers;

use app\modules\feed\lib\FeedExporter;
use app\modules\feed\models\Feed;
use app\modules\feed\models\FeedQueue;
use app\modules\feed\models\FeedRedirect;
use app\modules\feed\models\FeedSettings;
use app\modules\feed\models\forms\UploadFeed;
use app\modules\feed\models\QuickRedirect;
use app\modules\feed\models\search\FeedItemSearch;
use yii\data\ActiveDataProvider;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;

/**
 * Class FeedController
 * @package app\modules\feed\controllers
 */
class FeedController extends Controller
{
    /**
     * @inheritdoc
     */
    protected function allowActions()
    {
        return array_merge(
            parent::allowActions(),
            [
                'feed/download-feed'
            ]
        );
    }

    /**
     * @param $id
     * @return string
     */
    public function actionIndex($id)
    {
        $feed = $this->findModel($id);

        $feedUploadForm = new UploadFeed(['feed' => $feed]);
        $postData = $this->request->post();

        if ($feedUploadForm->load($postData)) {
            if ($feedUploadForm->process()) {
                return $this->redirect(Url::to(['/feed/feed', 'id' => $id]));
            }
        }

        $dataProvider = new ActiveDataProvider([
            'query' => FeedQueue::find()->andFilterWhere(['feed_id' => $id])
        ]);

        return $this->render('index', [
            'model' => $feedUploadForm,
            'dataProvider' => $dataProvider
        ]);
    }

    /**
     * @param int $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {
        $feedQueue = FeedQueue::findOne($id);

        if (!$feedQueue) {
            throw new NotFoundHttpException();
        }

        $feedId = $feedQueue->feed_id;
        if (file_exists($feedQueue->source_file)) {
            unlink($feedQueue->source_file);
        }

        if (file_exists($feedQueue->result_file)) {
            unlink($feedQueue->result_file);
        }

        $feedQueue->delete();

        return $this->redirect(Url::to(['/feed/feed', 'id' => $feedId]));
    }

    /**
     * @param $id
     * @return $this
     * @throws NotFoundHttpException
     */
    public function actionDownload($id)
    {
        $feedQueue = FeedQueue::findOne($id);

        if (!$feedQueue) {
            throw new NotFoundHttpException();
        }

        return \Yii::$app->response->sendFile($feedQueue->result_file, $feedQueue->original_filename);
    }

    /**
     * @param string $hash
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionRedirect($hash)
    {
        $feedRedirect = FeedRedirect::findOne(['hash_url' => $hash]);

        if (!$feedRedirect) {
            throw new NotFoundHttpException('Ссылка не найдена');
        }

        return $this->redirect($feedRedirect->target_url, 301);
    }

    /**
     * @param string $urlPart
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionQuickRedirect($urlPart)
    {
        /** @var QuickRedirect $quickRedirect */
        $quickRedirect = QuickRedirect::find()->andWhere(['source' => $urlPart])->one();

        if (!$quickRedirect) {
            throw new NotFoundHttpException();
        }

        return $this->redirect($quickRedirect->target, 301);
    }

    /**
     * Finds the Feed model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Feed the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Feed::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('Фид не найден');
        }
    }

    /**
     * Скачивание фида
     *
     * @param $feedId
     * @return $this
     * @throws NotFoundHttpException
     */
    public function actionDownloadFeed($feedId)
    {
        $feedSearch = new FeedItemSearch();

        $feedModel = Feed::findOne($feedId);

        if (!$feedModel) {
            throw new NotFoundHttpException();
        }

        $feedSettings = [];
        $feedSettingsModel = FeedSettings::findOne(['feed_id' => $feedId]);

        if ($feedSettingsModel) {
            $feedSettings = json_decode($feedSettingsModel->settings, true);
        }

        if (!empty($feedSettings)) {
            $feedSearch->load($feedSettings);
        }

        $feedSearch->load($this->request->queryParams, '');
        if (!empty($this->request->get('category_id'))) {
            $feedSearch->category_id = array_map(
                'intval', explode(',', $this->request->get('category_id'))
            );
        }

        if (!empty($this->request->get('brand_id'))) {
            $feedSearch->brand_id = array_map(
                'intval', explode(',', $this->request->get('brand_id'))
            );
        }

        if ($this->request->get('name')) {
            $feedSearch->name = $this->request->get('name');
        }

        $feedSearch->feed_id = $feedId;

        //echo $feedSearch->search()->query->createCommand()->getRawSql();die;

        $feedExporter = new FeedExporter($feedModel);
        $feed = $feedExporter->export($feedSearch->search()->query);

        $feedQueue = FeedQueue::find()
            ->andWhere(['feed_id' => $feedModel->id])
            ->orderBy(['id' => SORT_DESC])
            ->one();

        if ($this->request->get('view')) {
            $this->response->format = \yii\web\Response::FORMAT_RAW;
            $this->response->headers->add('Content-Type', 'text/xml');
            return $feed;
        } else {
            return \Yii::$app->response->sendContentAsFile($feed, $feedQueue->original_filename);
        }
    }
}
