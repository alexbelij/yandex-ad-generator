<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 17.10.16
 * Time: 16:10
 */

namespace app\lib\api\yandex\events;

use app\models\Account;
use yii\base\Event;

/**
 * Class RequestEvent
 * @package app\lib\api\yandex\events
 */
class RequestEvent extends Event
{
    /**
     * @var Account
     */
    public $account;

    /**
     * @var string
     */
    public $response;
}
