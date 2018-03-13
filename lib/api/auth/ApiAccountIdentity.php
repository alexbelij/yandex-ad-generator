<?php

namespace app\lib\api\auth;

use app\models\Account;

/**
 * Class TokenContainer
 * @package app\lib\api\auth
 */
class ApiAccountIdentity implements ApiIdentityInterface
{
    /**
     * @var Account
     */
    protected $account;

    /**
     * TokenContainer constructor.
     * @param Account $account
     */
    public function __construct($account)
    {
        $this->account = $account;
    }

    /**
     * @inheritDoc
     */
    public function getToken()
    {
        return $this->account->token;
    }

    /**
     * @inheritDoc
     */
    public function getAccount()
    {
        return $this->account;
    }
}
