<?php
namespace app\models\search;

use app\lib\api\shop\gateways\BrandsGateway;
use app\lib\api\shop\query\BrandQuery;
use app\models\Shop;
use app\models\Variation;

/**
 * Class BrandVariationSearch
 * @package app\models\search
 * @author Denis Dyadyun <sysadm85@gmail.com>
 */
class BrandVariationSearch extends VariationSearch
{
    /**
     * @inheritDoc
     */
    public function getType()
    {
        return Variation::TYPE_BRAND;
    }

    /**
     * @inheritDoc
     */
    public function getGateway(Shop $shop)
    {
        return BrandsGateway::factory($shop);
    }

    /**
     * @inheritDoc
     */
    public function getQuery()
    {
        $query = new BrandQuery();

        if (isset($this->onlyActive)) {
            $query->onlyActive($this->onlyActive);
        }

        if (!empty($this->ids)) {
            $query->byIds($this->ids);
        }

        if (!empty($this->name)) {
            $query->filterByTitle($this->name);
        }

        return $query;
    }

}
