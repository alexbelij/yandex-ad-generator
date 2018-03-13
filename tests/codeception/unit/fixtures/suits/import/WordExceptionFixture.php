<?php

namespace tests\codeception\unit\fixtures\suits\import;

use app\models\WordException;
use yii\test\ActiveFixture;

/**
 * Class WordExceptionFixture
 * @package tests\codeception\unit\fixtures
 */
class WordExceptionFixture extends ActiveFixture
{
    public $modelClass = WordException::class;
}