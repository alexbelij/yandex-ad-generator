<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 21.04.16
 * Time: 22:54
 */

namespace app\components;

class YandexConfig
{
    /**
     * @var array
     */
    private static $cache = [];

    /**
     * ВОзвращает конфиг
     *
     * @param string $name
     * @return mixed
     */
    public static function getConfig($name)
    {
        $fileName = self::getPath() . "$name.php";

        return require $fileName;
    }

    /**
     * Возвращает путь до конфига
     *
     * @return bool|string
     */
    public static function getPath()
    {
        return \Yii::getAlias('@app/config/yandex/');
    }
}
