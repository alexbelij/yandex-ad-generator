<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 12.03.16
 * Time: 16:53
 */

namespace app\lib\api\shop\gateways;

use app\lib\api\shop\query\BrandQuery;
use app\lib\api\shop\query\QueryInterface;
use app\models\ExternalProduct;

/**
 * Class BrandsGateway
 * @package app\lib\api\shop\gateways
 */
class BrandsGateway extends BaseGateway
{
    /**
     * @param array $ids
     * @param null $limit
     * @return array
     */
    public function getBrandsList($ids = [], $limit = null)
    {
        $query = (new BrandQuery())->onlyActive()->limit($limit);
        if (!empty($ids)) {
            $query->byIds($ids);
        }

        return $this->query($query);
    }
}
