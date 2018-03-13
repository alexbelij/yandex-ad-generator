<?php

namespace tests\codeception\unit\fixtures\suits\import;

use app\models\ExternalCategory;
use yii\test\ActiveFixture;

/**
 * Class ExternalCategoryFixture
 * @package tests\codeception\unit\fixtures
 */
class ExternalCategoryFixture extends ActiveFixture
{
    public $modelClass = ExternalCategory::class;
}
