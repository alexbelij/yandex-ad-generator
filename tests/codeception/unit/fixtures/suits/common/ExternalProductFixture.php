<?php

namespace tests\codeception\unit\fixtures\suits\common;

use app\models\ExternalProduct;
use tests\codeception\unit\fixtures\ActiveFixture;

/**
 * Class ExternalProductFixture
 * @package tests\codeception\unit\fixtures\suits\common
 */
class ExternalProductFixture extends ActiveFixture
{
    public $modelClass = ExternalProduct::class;
}
