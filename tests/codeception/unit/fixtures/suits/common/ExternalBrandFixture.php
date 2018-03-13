<?php

namespace tests\codeception\unit\fixtures\suits\common;

use app\models\ExternalBrand;
use tests\codeception\unit\fixtures\ActiveFixture;

/**
 * Class ExternalBrandFixture
 * @package tests\codeception\unit\fixtures\suits\common
 */
class ExternalBrandFixture extends ActiveFixture
{
    public $modelClass = ExternalBrand::class;
}
