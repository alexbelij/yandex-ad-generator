<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 08.10.16
 * Time: 14:32
 */

namespace app\lib\import\yml\strategies\factory;

use app\lib\import\yml\AbstractTagParser;

/**
 * Интерфейс фабрики парсеров атрибутов yml
 *
 * Interface YmlStrategyFactory
 * @package app\lib\import\yml\strategies\factory
 */
interface TagParserFactoryInterface
{
    /**
     * @return AbstractTagParser
     */
    public function createCategoriesTagParser();

    /**
     * @return AbstractTagParser
     */
    public function createCategoryTagParser();

    /**
     * @return AbstractTagParser
     */
    public function createOffersTagParser();

    /**
     * @return AbstractTagParser
     */
    public function createOfferTagParser();

    /**
     * @return AbstractTagParser
     */
    public function createShopTagParser();

    /**
     * @return AbstractTagParser
     */
    public function createYmlCatalogTagParser();
}
