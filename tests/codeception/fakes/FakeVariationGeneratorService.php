<?php

namespace tests\codeception\fakes;

use app\lib\api\shop\models\ExtProduct;
use app\lib\variationStrategies\DefaultStrategy;

/**
 * Class FakeVariationGeneratorService
 * @package tests\codeception\fakes
 * @author Denis Dyadyun <sysadm85@gmail.com>
 */
class FakeVariationGeneratorService extends DefaultStrategy
{
    /**
     * @var array
     */
    public $brandVariations;

    /**
     * @var array
     */
    public $categoryVariations;

    /**
     * @var array
     */
    public $templates;

    /**
     * @inheritDoc
     */
    public function __construct()
    {

    }


    /**
     * @inheritDoc
     */
    public function getBrandVariations(ExtProduct $product)
    {
        return $this->brandVariations;
    }

    /**
     * @inheritDoc
     */
    public function getCategoryVariations(ExtProduct $product)
    {
        return $this->categoryVariations;
    }

    /**
     * @inheritDoc
     */
    protected function getAllAvailableTemplates()
    {
        return $this->templates;
    }
}
