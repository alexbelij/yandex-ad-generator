<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 25.08.16
 * Time: 9:34
 */

namespace app\lib\api\shop\dataSource;

use app\lib\api\shop\ApiResult;
use app\lib\api\shop\exceptions\ApiGatewayException;
use app\lib\api\shop\query\QueryInterface;
use app\models\Shop;

/**
 * Interface GatewayInterface
 * @package app\lib\api\shop\gateways
 */
interface DataSourceInterface
{
    /**
     * @param QueryInterface $query
     * @return mixed
     */
    public function findByQuery(QueryInterface $query);

    /**
     * @param $ids
     * @return mixed
     */
    public function findByIds($ids);

    /**
     * Выполнение запроса
     *
     * @param array|QueryInterface $query
     * @return array
     */
    public function query($query);

    /**
     * Возвращает общее количество элементов
     * @param array|QueryInterface $query
     * @return mixed
     * @throws ApiGatewayException
     */
    public function totalCount($query);

    /**
     * @return Shop
     */
    public function getShop();
}
