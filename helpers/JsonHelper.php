<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 22.10.16
 * Time: 17:42
 */

namespace app\helpers;

use yii\base\Model;
use yii\helpers\Json;

/**
 * Class JsonHelper
 * @package app\helpers
 */
class JsonHelper extends Json
{
    /**
     * @param Model $model
     * @return string
     */
    public static function encodeModelPretty(Model $model)
    {
        return json_encode($model->toArray(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}