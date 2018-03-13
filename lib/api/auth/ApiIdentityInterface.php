<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 15.10.16
 * Time: 23:53
 */

namespace app\lib\api\auth;
use app\models\Account;

/**
 * Interface TokenInterface
 * @package app\lib\api\yandex\auth
 */
interface ApiIdentityInterface
{
    /**
     * @return mixed
     */
    public function getToken();

    /**
     * @return Account
     */
    public function getAccount();
}
