<?php

namespace app\lib\api\shop\gateways;

use app\lib\api\shop\dataSource\InternalDataSource;
use app\models\ExternalProduct;
use app\models\Shop;

/**
 * Class InternalProductsGateway
 * Класс шлюза для работы с загруженными товарами как с полученными через апи
 *
 * @package app\lib\api\shop\gateways
 */
class InternalProductsGateway extends ProductsGateway
{
    /**
     * @inheritDoc
     */
    public static function factory(Shop $shop)
    {
        return new static(new InternalDataSource(ExternalProduct::className(), $shop));
    }
}
