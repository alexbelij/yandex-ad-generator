<?php

namespace tests\codeception\unit\fixtures\suits\import;

use app\models\VariationItem;
use yii\test\ActiveFixture;

/**
 * Class VariationItemFixture
 * @package tests\codeception\unit\fixtures\suits\import
 */
class VariationItemFixture extends ActiveFixture
{
    public $modelClass = VariationItem::class;
}