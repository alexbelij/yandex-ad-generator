<?php

namespace app\controllers;

use Yii;
use app\models\FileImportLog;
use app\models\search\FileImportLogSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * FileImportLogController implements the CRUD actions for FileImportLog model.
 */
class FileImportLogController extends BaseController
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
     * Lists all FileImportLog models.
     * @return mixed
     */
    public function actionIndex($fileImportId)
    {
        $searchModel = new FileImportLogSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $searchModel->file_import_id = $fileImportId;

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
}
