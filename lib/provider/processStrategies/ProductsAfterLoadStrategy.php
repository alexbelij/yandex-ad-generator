<?php

namespace app\lib\provider\processStrategies;

use app\lib\api\shop\mapper\ProductMapper;
use app\models\ExternalProduct;
use app\models\Product;
use app\models\Shop;
use yii\base\Object;
use yii\helpers\ArrayHelper;

/**
 * Class ProductsAfterLoadStrategy
 * @package app\lib\provider\processStrategies
 * @author Denis Dyadyun <sysadm85@gmail.com>
 */
class ProductsAfterLoadStrategy extends Object implements AfterLoadProcessStrategy
{
    /**
     * @var int
     */
    public $shopId;

    /**
     * @param int[] $ids
     * @return array|\yii\db\ActiveRecord[]
     */
    protected function getProducts($ids)
    {
        return Product::find()
            ->andWhere(['product_id' => $ids, 'shop_id' => $this->shopId])
            ->asArray()
            ->indexBy('product_id')
            ->all();
    }

    /**
     * @param ExternalProduct[] $models
     * @return array
     */
    public function process($models)
    {
        if (empty($models)) {
            return [];
        }

        $models = (new ProductMapper(Shop::findOne($this->shopId)))->createResult($models);
        $keys = ArrayHelper::getColumn($models, 'id');
        $products = $this->getProducts($keys);

        foreach ($models as $i => $item) {
            $item['price'] = round($item['price']);
            if (isset($products[$item['id']])) {
                $product = $products[$item['id']];
                $productAdditional = [
                    'manual_price' => $product['manual_price'],
                    'our_id' => $product['id']
                ];
            } else {
                $productAdditional = [
                    'manual_price' => null,
                    'our_id' => null
                ];
            }
            $productAdditional['created_at'] = ArrayHelper::getValue($item, 'created_at');
            $productAdditional['updated_at'] = ArrayHelper::getValue($item, 'updated_at');

            $models[$i] = array_merge($item, $productAdditional);
        }

        return $models;
    }
}
