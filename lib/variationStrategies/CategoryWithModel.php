<?php

namespace app\lib\variationStrategies;

use app\lib\variationStrategies\generationStrategies\CategoryModelStrategy;
use app\lib\variationStrategies\generationStrategies\RotationStrategy;
use app\lib\variationStrategies\generationStrategies\TitleVariantsStrategy;
use app\lib\variationStrategies\generationStrategies\WithPriceWordStrategy;

/**
 * Class CategoryWithModel
 * @package app\lib\variationStrategies
 */
class CategoryWithModel extends WithoutNumbers
{
    /**
     * @var array
     */
    protected $generationStrategies = [
        TitleVariantsStrategy::class,
        WithPriceWordStrategy::class,
        RotationStrategy::class,
        CategoryModelStrategy::class,
    ];
}
