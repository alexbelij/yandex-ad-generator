<?php

namespace app\modules\bidManager\controllers;

use app\modules\bidManager\lib\traits\EditableTrait;
use app\modules\bidManager\models\accounts\YandexAccount;
use Yii;
use app\modules\bidManager\models\Account;
use app\modules\bidManager\models\search\AccountSearch;
use yii\base\ErrorException;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * AccountsController implements the CRUD actions for Account model.
 */
class AccountsController extends Controller
{
    use EditableTrait;

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
     * Lists all Account models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new AccountSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Account model.
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
     * Creates a new Account model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new YandexAccount();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Account model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Account model.
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
     * Finds the Account model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Account the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = YandexAccount::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     * Обновление токена
     *
     * @param string $code
     * @param string $state
     * @return \yii\web\Response
     * @throws BadRequestHttpException
     */
    public function actionTokenUpdate($code, $state)
    {
        $state = urldecode($state);
        $stateData = [];
        parse_str($state, $stateData);
        if (empty($stateData['bid_account_id'])) {
            throw new BadRequestHttpException();
        }

        /** @var YandexAccount $account */
        $account = YandexAccount::findOne($stateData['bid_account_id']);

        // Формирование параметров (тела) POST-запроса с указанием кода подтверждения
        $query = array(
            'grant_type' => 'authorization_code',
            'code' => $code,
            'client_id' => $account->yandexApplicationId,
            'client_secret' => $account->yandexSecret
        );
        $query = http_build_query($query);

        // Формирование заголовков POST-запроса
        $header = "Content-type: application/x-www-form-urlencoded";

        // Выполнение POST-запроса и вывод результата
        $opts = array('http' =>
            array(
                'method'  => 'POST',
                'header'  => $header,
                'content' => $query
            )
        );
        $context = stream_context_create($opts);
        try {
            $result = file_get_contents('https://oauth.yandex.ru/token', false, $context);
        } catch (ErrorException $e) {
            echo $e->getMessage();
            return;
        }

        $result = json_decode($result);

        if (empty($result->access_token)) {
            throw new BadRequestHttpException('Empty token');
        }

        $account->token = $result->access_token;
        $account->save();

        return $this->redirect(['/bid-manager/accounts']);
    }
}
