<?php

namespace app\lib\variationStrategies;

use app\lib\api\shop\models\ExtProduct;
use app\models\Ad;

/**
 * Interface VariationStrategyInterface
 * @package app\lib\variationStrategies
 */
interface VariationStrategyInterface
{
    /**
     * @param ExtProduct $product
     * @return mixed
     */
    public function generate(ExtProduct $product);

    /**
     * @param array $ads
     * @return mixed
     */
    public function addVariationsFromAds(array $ads);

    /**
     * @param $variation
     * @return mixed
     */
    public function addVariation($variation);

    /**
     * @param ExtProduct $product
     * @return mixed
     */
    public function generateKeywords(ExtProduct $product);

    /**
     * @param Ad $ad
     * @param ExtProduct $extProduct
     * @return mixed
     */
    public function adHasCurrentBrands(Ad $ad, ExtProduct $extProduct);
}
