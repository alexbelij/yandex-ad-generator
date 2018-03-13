<?php

namespace tests\codeception\unit\fixtures\suits\common;

use app\models\Shop;
use tests\codeception\unit\fixtures\ActiveFixture;

/**
 * Class ShopFixture
 * @package tests\codeception\unit\fixtures
 */
class ShopFixture extends ActiveFixture
{
    public $modelClass = Shop::class;
}