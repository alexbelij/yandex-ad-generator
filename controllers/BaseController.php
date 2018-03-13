<?php

namespace app\controllers;

use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\Request;
use yii\web\Response;

/**
 * Class BaseController
 * @package app\controllers
 *
 * @property Request $request
 * @property Response $response
 */
class BaseController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['login', 'logout'],
                'rules' => [
                    [
                        'actions' => ['login'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Список разрешенных экшенов для неавторизованного пользователя
     *
     * @return array
     */
    protected function allowActions()
    {
        return [
            'security/*'
        ];
    }

    /**
     * @inheritDoc
     */
    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        if ($this->getUser()->isGuest) {
            foreach ($this->allowActions() as $allowAction) {
                list($controller, $act) = explode('/', $allowAction);
                if (\Yii::$app->controller->id == $controller
                    && (\Yii::$app->controller->action->id == $act
                        || $act == '*')
                ) {
                    return true;
                }
            }

            return $this->redirect(Url::to(['/login']));
        }

        return true;
    }

    /**
     * @return string
     */
    public function actionError()
    {
        if (\Yii::$app->user->isGuest) {
            header('Location: /login');
            die;
        } else {
            $this->layout = 'simple';
            $exception = \Yii::$app->errorHandler->exception;
            if ($exception !== null) {
                return $this->render('error', ['message' => $exception->getMessage()]);
            }
        }
    }

    /**
     * @return \yii\console\Request|\yii\web\Request
     */
    public function getRequest()
    {
        return \Yii::$app->request;
    }

    /**
     * @return \yii\console\Response|Response
     */
    public function getResponse()
    {
        return \Yii::$app->response;
    }

    /**
     * @return mixed|\yii\web\User
     */
    protected function getUser()
    {
        return \Yii::$app->user;
    }
}
