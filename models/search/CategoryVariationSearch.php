<?php

namespace app\models\search;

use app\lib\api\shop\gateways\CategoriesGateway;
use app\lib\api\shop\query\CategoryQuery;
use app\models\Shop;
use app\models\Variation;

class CategoryVariationSearch extends VariationSearch
{
    /**
     * @var int
     */
    public $parentId;

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['parentId'], 'safe']
        ]);
    }

    /**
     * @return string
     */
    public function getType()
    {
        return Variation::TYPE_CATEGORY;
    }

    /**
     * @param Shop $shop
     * @return CategoriesGateway
     */
    public function getGateway(Shop $shop)
    {
        return CategoriesGateway::factory($shop);
    }

    /**
     * @return CategoryQuery
     */
    public function getQuery()
    {
        $query = new CategoryQuery();
        if (!empty($this->parentId)) {
            $query->byParentId($this->parentId);
        }
        
        if ($this->ids) {
            $query->byIds($this->ids);
        }
        
        if ($this->name) {
            $query->filterByTitle($this->name);
        }
        
        return $query;
    }
}
