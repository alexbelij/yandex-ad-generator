<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 10.10.16
 * Time: 18:55
 */

namespace app\helpers;

/**
 * Class ArrayHelper
 * @package app\helpers
 */
class ArrayHelper extends \yii\helpers\ArrayHelper
{
    /**
     * Возвращает декартово произведение элементов
     *
     * @param array $items
     * @return array
     */
    public static function getCartesianComposition(array $items)
    {
        if (count($items) <= 1) {
            return $items;
        }

        return self::_getComposition(0, $items);
    }

    /**
     * @param $index
     * @param array $items
     * @return array
     */
    protected static function _getComposition($index, array $items)
    {
        $result = [];
        if ($index == count($items)) {
            $result[] = [];
        } else {
            foreach ($items[$index] as $item) {
                foreach (self::_getComposition($index + 1, $items) as $set) {
                    $set[] = $item;
                    $result[] = $set;
                }
            }
        }

        return $result;
    }

    /**
     * @param array $items
     * @param mixed $key
     * @return array
     */
    public static function groupBy(array $items, $key)
    {
        $result = [];
        foreach ($items as $item) {
            $result[ArrayHelper::getValue($item, $key)][] = $item;
        }

        return $result;
    }

    /**
     * Поиск в коллекции используя callback
     *
     * @param array $collection
     * @param callable $what
     * @param bool $preserveKeys
     * @return array
     */
    public static function findByCallback(array $collection, callable $what, $preserveKeys = false)
    {
        $result = [];
        $pos = 0;
        foreach ($collection as $key => $item) {
            if (call_user_func($what, $item)) {
                $index = $preserveKeys ? $key : $pos++;
                $result[$index] = $item;
            }
        }
        return $result;
    }
    /**
     * Поиск в коллекции используя массив критерий
     *
     * @param array $collection
     * @param array $what
     * @param bool $preserveKeys
     * @return array
     */
    public static function findByArray(array $collection, array $what, $preserveKeys = false)
    {
        $result = [];
        $pos = 0;
        foreach ($collection as $key => $item) {
            $isFound = true;
            foreach ($what as $field => $value) {
                if (self::getValue($item, $field) != $value) {
                    $isFound = false;
                    break;
                }
            }
            if ($isFound) {
                $index = $preserveKeys ? $key : $pos++;
                $result[$index] = $item;
            }
        }
        return $result;
    }
    /**
     * Поиск в коллекции
     *
     * @param array $collection
     * @param array|callable $what
     * @param bool $preserveKeys
     * @return array
     */
    public static function find(array $collection, $what, $preserveKeys = false)
    {
        if (is_callable($what)) {
            return self::findByCallback($collection, $what, $preserveKeys);
        } elseif (is_array($what)) {
            return self::findByArray($collection, $what, $preserveKeys);
        } else {
            return [];
        }
    }

    /**
     * Flatten a multi-dimensional array into a single level.
     *
     * @param  array $array
     * @param  int $depth
     *
     * @return array
     */
    public static function flatten($array, $depth = INF)
    {
        $result = [];
        foreach ($array as $item) {
            if (is_array($item)) {
                if ($depth === 1) {
                    $result = array_merge($result, $item);
                    continue;
                }
                $result = array_merge($result, static::flatten($item, $depth - 1));
                continue;
            }
            $result[] = $item;
        }
        return $result;
    }

    /**
     * @param array $array
     * @return mixed
     */
    public static function first($array)
    {
        return reset($array);
    }
}
