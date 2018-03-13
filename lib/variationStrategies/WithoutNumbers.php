<?php

namespace app\lib\variationStrategies;

use app\lib\api\shop\models\ExtProduct;

/**
 * Class WithoutNumbers
 * @package app\lib\variationStrategies
 */
class WithoutNumbers extends DefaultStrategy
{
    /**
     * @var boolean
     */
    protected $hasNumbers;

    /**
     * @inheritDoc
     */
    protected function beforeGenerateKeywords(ExtProduct $product)
    {
        if (!parent::beforeGenerateKeywords($product)) {
            return false;
        }

        $this->hasNumbers = preg_match('#\d#', $product->title);

        return true;
    }

    /**
     * @inheritDoc
     */
    protected function afterGenerateVariations(array $variations)
    {
        $variations = parent::afterGenerateVariations($variations);

        if (!$this->hasNumbers) {
            return $variations;
        }

        $result = [];
        foreach ($variations as $variation) {
            if (preg_match('#\d#', $variation)) {
                $result[] = $variation;
            }
        }

        return $result;
    }

}
