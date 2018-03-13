<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 21.04.16
 * Time: 20:35
 */

namespace app\controllers;

use app\models\Account;
use yii\base\ErrorException;
use yii\web\BadRequestHttpException;

/**
 * Class YandexTokenController
 * @package app\controllers
 */
class YandexTokenController extends BaseController
{
    /**
     * Обновление токена
     *
     * @param string $code
     * @param string $state
     * @return \yii\web\Response
     * @throws BadRequestHttpException
     */
    public function actionUpdate($code, $state)
    {
        $state = urldecode($state);
        $stateData = [];
        parse_str($state, $stateData);
        if (empty($stateData['account_id'])) {
            throw new BadRequestHttpException();
        }

        /** @var Account $account */
        $account = Account::findOne($stateData['account_id']);

        // Формирование параметров (тела) POST-запроса с указанием кода подтверждения
        $query = array(
            'grant_type' => 'authorization_code',
            'code' => $code,
            'client_id' => $account->account_data['yandex_application_id'],
            'client_secret' => $account->account_data['yandex_secret']
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

        return $this->redirect(['/accounts']);
    }
}
