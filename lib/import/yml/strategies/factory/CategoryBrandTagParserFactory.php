<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 08.10.16
 * Time: 14:57
 */

namespace app\lib\import\yml\strategies\factory;

use app\lib\import\yml\strategies\categoryBrandStrategy\CategoriesTagParser;
use app\lib\import\yml\strategies\defaultStrategy\CategoryTagParser;
use app\lib\import\yml\strategies\categoryBrandStrategy\OfferTagParser;

/**
 * Стратегия получения брендов из категорий
 *
 * Class CategoryBrandFactory
 * @package app\lib\import\yml\strategies\factory
 */
class CategoryBrandTagParserFactory extends DefaultTagParserFactory
{
    /**
     * @var CategoriesTagParser
     */
    protected $categoriesTagParser;

    /**
     * @inheritDoc
     */
    public function createCategoriesTagParser()
    {
        return $this->getCategoriesTagParser();
    }

    /**
     * @inheritDoc
     */
    public function createCategoryTagParser()
    {
        return new CategoryTagParser($this->fileImport, $this->logger, $this->getCategoriesTagParser());
    }

    /**
     * @inheritDoc
     */
    public function createOfferTagParser()
    {
        $categoriesParser = $this->createCategoriesTagParser();
        return new OfferTagParser($this->fileImport, $this->logger, $categoriesParser->getCategories());
    }

    /**
     * @return CategoriesTagParser
     */
    protected function getCategoriesTagParser()
    {
        if (is_null($this->categoriesTagParser)) {
            $this->categoriesTagParser = new CategoriesTagParser($this->fileImport, $this->logger);
        }

        return $this->categoriesTagParser;
    }
}
