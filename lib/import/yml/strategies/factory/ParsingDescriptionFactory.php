<?php

namespace app\lib\import\yml\strategies\factory;

use app\lib\import\yml\extensions\ColorsFromOfferExtension;
use app\lib\import\yml\strategies\defaultStrategy\OfferTagParser;

/**
 * Фабрика парсеров с учетом парсинга описания цветов товара
 *
 * Class ParsingDescriptionFactory
 * @package app\lib\import\yml\strategies\factory
 */
class ParsingDescriptionFactory extends DefaultTagParserFactory
{
    /**
     * @inheritDoc
     */
    public function createOfferTagParser()
    {
        $categoriesTagParser = $this->createCategoriesTagParser();
        $offerTagParser = new OfferTagParser($this->fileImport, $this->logger, $categoriesTagParser->getCategories());

        return $offerTagParser->setExtensions([
            ColorsFromOfferExtension::class
        ]);
    }

}
