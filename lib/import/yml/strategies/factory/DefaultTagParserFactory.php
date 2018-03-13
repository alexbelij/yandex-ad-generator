<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 08.10.16
 * Time: 14:36
 */

namespace app\lib\import\yml\strategies\factory;

use app\components\LoggerInterface;
use app\lib\import\yml\AbstractTagParser;
use app\lib\import\yml\strategies\defaultStrategy\CategoriesTagParser;
use app\lib\import\yml\strategies\defaultStrategy\CategoryTagParser;
use app\lib\import\yml\strategies\defaultStrategy\OfferTagParser;
use app\lib\import\yml\strategies\defaultStrategy\ShopTagParser;
use app\lib\import\yml\strategies\defaultStrategy\YmlCatalogTagParser;
use app\lib\import\yml\StubTag;
use app\models\FileImport;

/**
 * Стратегия парсинга атрибутов "по умолчанию"
 *
 * Class DefaultStrategyFactory
 * @package app\lib\import\yml\strategies\factory
 */
class DefaultTagParserFactory extends AbstractTagParserFactory
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
        if (!$this->categoriesTagParser) {
            $this->categoriesTagParser = new CategoriesTagParser($this->fileImport, $this->logger);
        }

        return $this->categoriesTagParser;
    }

    /**
     * @inheritDoc
     */
    public function createCategoryTagParser()
    {
        return new CategoryTagParser($this->fileImport, $this->logger, $this->createCategoriesTagParser());
    }

    /**
     * @inheritDoc
     */
    public function createOffersTagParser()
    {
        return new StubTag($this->fileImport, $this->logger);
    }

    /**
     * @inheritDoc
     */
    public function createOfferTagParser()
    {
        $categoriesTagParser = $this->createCategoriesTagParser();
        return new OfferTagParser($this->fileImport, $this->logger, $categoriesTagParser->getCategories());
    }

    /**
     * @inheritDoc
     */
    public function createShopTagParser()
    {
        return new ShopTagParser($this->fileImport, $this->logger);
    }

    /**
     * @inheritDoc
     */
    public function createYmlCatalogTagParser()
    {
        return new YmlCatalogTagParser($this->fileImport, $this->logger);
    }
}
