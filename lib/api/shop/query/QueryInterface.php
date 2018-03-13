<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 08.03.16
 * Time: 22:07
 */

namespace app\lib\api\shop\query;

use app\lib\api\shop\dataSource\DataSourceInterface;

/**
 * Interface QueryInterface
 * @package app\lib\api\shop\query
 */
interface QueryInterface
{
    /**
     * Возвращает массив параметров для запроса
     *
     * @param DataSourceInterface $dataSource
     * @return mixed
     */
    public function getQuery(DataSourceInterface $dataSource);
}