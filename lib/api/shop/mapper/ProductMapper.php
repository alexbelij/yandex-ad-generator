<?php

namespace app\lib\api\shop\mapper;

use app\models\ExternalProduct;
use app\models\Shop;
use yii\helpers\ArrayHelper;

/**
 * Class ProductQueryMapper
 * @package app\lib\api\shop\mapper
 */
class ProductMapper extends BaseMapper
{
    /**
     * @param ExternalProduct $item
     * @return array
     */
    protected function prepareItem($item)
    {
        $isApiStrategy = ($this->shop->external_strategy == Shop::EXTERNAL_STRATEGY_API);

        return [
            'id' => $item->id,
            'title' => $item->title,
            'href' => $item->url,
            'categories' => [
                [
                    'id' => $isApiStrategy ? ArrayHelper::getValue($item, 'category.outer_id') : $item->category_id,
                    'title' => ArrayHelper::getValue($item, 'category.title')
                ]
            ],
            'brand' => [
                'id' => $isApiStrategy ? ArrayHelper::getValue($item, 'brand.outer_id') : $item->brand_id,
                'title' => ArrayHelper::getValue($item, 'brand.title')
            ],
            'price' => $item->price,
            'is_available' => $item->is_available,
            'created_at' => $item->created_at,
            'updated_at' => $item->updated_at,
            'type_prefix' => $item->type_prefix
        ];
    }
}
