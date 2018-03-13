<?php

namespace tests\codeception\unit\fixtures\suits\import;

use app\models\Variation;
use yii\test\ActiveFixture;

/**
 * Class VariationFixture
 * @package tests\codeception\unit\fixtures\suits\import
 */
class VariationFixture extends ActiveFixture
{
    public $modelClass = Variation::class;
}