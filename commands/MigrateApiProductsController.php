<?php

namespace app\commands;

use app\models\ExternalProduct;
use app\models\Product;
use app\models\Shop;
use yii\console\Controller;

/**
 * Class MigrateApiProductsController
 * @package app\commands
 */
class MigrateApiProductsController extends Controller
{
    /**
     * Миграция товаров на внутренний id товаров
     */
    public function actionIndex()
    {
        /** @var Shop[] $shops */
        $shops = Shop::find()
            ->andWhere(['external_strategy' => Shop::EXTERNAL_STRATEGY_API])
            ->all();

        foreach ($shops as $shop) {
            $productsQuery = Product::find()
                ->andWhere(['shop_id' => $shop->id]);

            /** @var Product[] $products */
            foreach ($productsQuery->batch(1000) as $products) {
                foreach ($products as $product) {
                    /** @var ExternalProduct $externalProduct */
                    $externalProduct = ExternalProduct::find()
                        ->andWhere(['outer_id' => $product->product_id])
                        ->one();
                    if ($externalProduct) {
                        $product->product_id = $externalProduct->id;
                        $product->save();
                    }
                }
            }
        }
    }
}
