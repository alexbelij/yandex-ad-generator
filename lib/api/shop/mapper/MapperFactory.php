<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 28.08.16
 * Time: 8:47
 */

namespace app\lib\api\shop\mapper;

use app\models\ExternalBrand;
use app\models\ExternalCategory;
use app\models\ExternalProduct;
use app\models\Shop;
use yii\base\Exception;

/**
 * Class MapperFactory
 * @package app\lib\api\shop\mapper
 */
class MapperFactory
{
    /**
     * @param string $modelClass
     * @param Shop $shop
     * @return BrandMapper|CategoryMapper|ProductMapper
     * @throws Exception
     */
    public static function create($modelClass, Shop $shop)
    {
        switch ($modelClass) {
            case ExternalProduct::className():
                return new ProductMapper($shop);
            case ExternalCategory::className():
                return new CategoryMapper($shop);
            case ExternalBrand::className():
                return new BrandMapper($shop);
        }

        throw new Exception('Маппер не найден');
    }
}