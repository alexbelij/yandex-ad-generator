<?php

namespace tests\codeception\unit\fixtures\suits\common;

use app\models\Ad;
use tests\codeception\unit\fixtures\ActiveFixture;

/**
 * Class AdFixture
 * @package tests\codeception\unit\fixtures\suits\common
 */
class AdFixture extends ActiveFixture
{
    public $modelClass = Ad::class;
}
