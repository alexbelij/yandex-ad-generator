<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 05.10.16
 * Time: 19:43
 */

namespace app\lib\api\shop\gateways\cached;

/**
 * Class BrandsCachedGateway
 * @package app\lib\api\shop\services
 */
class BrandsCachedGateway extends CachedGatewayList
{
    /**
     * @inheritDoc
     */
    protected function getData()
    {
        return $this->gateway->getBrandsList();
    }

}
