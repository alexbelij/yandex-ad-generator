<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 28.11.16
 * Time: 21:09
 */

namespace tests\codeception\unit\fixtures\suits\import;

use app\models\ExternalBrand;
use yii\test\ActiveFixture;

/**
 * Class ExternalBrandFixture
 * @package tests\codeception\unit\fixtures
 */
class ExternalBrandFixture extends ActiveFixture
{
    public $modelClass = ExternalBrand::class;
}