<?php

namespace app\lib\api\shop\query\translator;

use app\models\ExternalBrand;
use app\models\ExternalCategory;
use app\models\ExternalProduct;
use app\models\Shop;

/**
 * Class ProductTranslator
 * @package app\lib\api\shop\query\translator
 */
class ProductTranslator extends AbstractInternalTranslator
{
    /**
     * @inheritDoc
     */
    protected function getQuery()
    {
        return ExternalProduct::find();
    }

    /**
     * @inheritDoc
     */
    public function translate(array $params)
    {
        $query = parent::translate($params);

        $query->joinWith(['category', 'brand']);

        if (!empty($params['title'])) {
            $query->andWhere([
                'OR',
                ['LIKE', ExternalProduct::tableName() . '.title', $params['title']],
                ['LIKE', ExternalCategory::tableName() . '.title', $params['title']],
                ['LIKE', ExternalBrand::tableName() . '.title', $params['title']]
            ]);
        }

        if (!empty($params['brand_id'])) {
            $query->andWhere(['brand_id' => $params['brand_id']]);
        }

        if (!empty($params['price_from'])) {
            $query->andWhere(['>=', 'price', $params['price_from']]);
        }

        if (!empty($params['price_to'])) {
            $query->andWhere(['<=', 'price', $params['price_to']]);
        }

        if (!empty($params['category_id'])) {
            $query->andWhere(['category_id' => $params['category_id']]);
        }

        return $query;
    }
}
