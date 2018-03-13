<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 08.11.16
 * Time: 17:36
 */

namespace app\lib\services;

use app\lib\api\shop\gateways\BrandsGateway;
use app\models\ExternalProduct;

/**
 * Class BrandsService
 * @package app\lib\services
 */
class BrandService
{
    /**
     * @var BrandsGateway
     */
    protected $brandsGateway;

    /**
     * BrandsService constructor.
     * @param BrandsGateway $brandsGateway
     */
    public function __construct(BrandsGateway $brandsGateway)
    {
        $this->brandsGateway = $brandsGateway;
    }

    /**
     * @param array $categoryIds
     * @return array|mixed
     */
    public function getAvailableBrands($categoryIds = [])
    {
        $brandIds = null;
        if (!empty($categoryIds)) {
            $brandIds = ExternalProduct::find()
                ->select('brand_id')
                ->distinct()
                ->andWhere(['category_id' => $categoryIds])
                ->column();
        }

        return $this->brandsGateway->getBrandsList($brandIds);
    }
}
