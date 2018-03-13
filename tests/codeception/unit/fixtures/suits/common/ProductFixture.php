<?php

namespace tests\codeception\unit\fixtures\suits\common;

use app\models\Product;
use tests\codeception\unit\fixtures\ActiveFixture;

/**
 * Class ProductFixture
 * @package tests\codeception\unit\fixtures\suits\common
 */
class ProductFixture extends ActiveFixture
{
    public $modelClass = Product::class;
}
