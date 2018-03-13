<?php

namespace app\lib\variationStrategies;

use app\models\Shop;
use yii\base\Exception;

/**
 * Class VariationStrategyFactory
 * @package app\lib\variationStrategies
 */
class VariationStrategyFactory
{
    const DEFAULT_STRATEGY = 'default';
    const WITHOUT_NUMBERS_STRATEGY = 'withoutNumbers';
    const MAIN_CATEGORY_WITH_MODEL = 'mainCategoryWithModel';

    /**
     * @var array
     */
    protected $map = [
        self::DEFAULT_STRATEGY => DefaultStrategy::class,
        self::WITHOUT_NUMBERS_STRATEGY => WithoutNumbers::class,
        self::MAIN_CATEGORY_WITH_MODEL => CategoryWithModel::class,
    ];

    /**
     * @param Shop $shop
     * @return VariationStrategyInterface
     * @throws Exception
     */
    public function factory(Shop $shop)
    {
        $variationStrategy = $shop->variation_strategy;
        if (!isset($this->map[$variationStrategy])) {
            $variationStrategy = 'default';
        }

        $class = $this->map[$variationStrategy];

        return new $class($shop);
    }
}
