<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * Class BaseModel
 * @package app\models
 */
class BaseModel extends ActiveRecord
{
    const DATETIME_FORMAT = 'Y-m-d H:i:s';

    /**
     * @return string
     */
    public static function shortClassName()
    {
        $className = get_called_class();

        return substr($className, strrpos($className, '\\') + 1);
    }

    /**
     * @param string $name
     * @return string
     */
    public static function fullColumn($name)
    {
        return static::tableName() . '.' . $name;
    }
}
