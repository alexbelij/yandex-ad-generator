<?php

namespace app\controllers;

use app\lib\tasks\AbstractTask;
use app\models\search\UpdateLogSearch;
use Yii;
use app\models\TaskQueue;
use app\models\search\TaskQueueSearch;
use yii\helpers\FileHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * TaskQueueController implements the CRUD actions for TaskQueue model.
 */
class TaskQueueController extends BaseController
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
     * Lists all TaskQueue models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new TaskQueueSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    
    public function actionDetails($task_id)
    {
        $searchModel = new UpdateLogSearch();

        $searchModel->task_id = $task_id;
        
        $dataProvider = $searchModel->search($this->request->queryParams);
        
        return $this->render('details', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel
        ]);
    }
    
    /**
     * Deletes an existing TaskQueue model.
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
     * Finds the TaskQueue model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return TaskQueue the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = TaskQueue::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     * @param int $taskId
     * @throws NotFoundHttpException
     */
    public function actionDownloadLog($taskId)
    {
        $task = TaskQueue::findOne($taskId);

        if (!$task) {
            throw new NotFoundHttpException();
        }

        $file = $task->log_file;

        $file = AbstractTask::getTaskLogDir() . $file;

        if (!file_exists($file)) {
            throw new NotFoundHttpException("Файл не найден: '$file'");
        }

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . basename($file));
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));
        // читаем файл и отправляем его пользователю
        readfile($file);
        exit;
    }
}
