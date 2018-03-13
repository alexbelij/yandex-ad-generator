<?php

namespace tests\codeception\unit\fixtures\suits\common;

use app\models\Account;
use tests\codeception\unit\fixtures\ActiveFixture;

/**
 * Class AccountFixture
 * @package tests\codeception\unit\fixtures
 */
class AccountFixture extends ActiveFixture
{
    public $modelClass = Account::class;
}