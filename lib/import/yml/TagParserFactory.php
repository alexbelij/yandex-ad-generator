<?php

namespace app\lib\import\yml;

use app\components\LoggerInterface;
use app\lib\import\yml\strategies\factory\TagParserFactoryInterface;
use app\models\FileImport;
use app\models\Shop;
use yii\base\Exception;

/**
 * Фабрика стратегий парсинга тегов
 *
 * Class TagParserFactory
 * @package app\lib\import\yml
 */
class TagParserFactory
{
    /**
     * Маппинг типа стратегии парсинга и абстрактной фабрики
     *
     * @var array
     */
    protected static $factoryStrategyMap = [
        Shop::PARSE_STRATEGY_DEFAULT => 'app\lib\import\yml\strategies\factory\DefaultTagParserFactory',
        Shop::PARSE_STRATEGY_BRAND => 'app\lib\import\yml\strategies\factory\CategoryBrandTagParserFactory',
        Shop::PARSE_STRATEGY_DESCRIPTION => 'app\lib\import\yml\strategies\factory\ParsingDescriptionFactory'
    ];

    /**
     * @param FileImport $fileImport
     * @param LoggerInterface $logger
     * @param Shop $shop
     * @return TagParserFactoryInterface
     * @throws Exception
     */
    public function create(FileImport $fileImport, LoggerInterface $logger, Shop $shop)
    {
        $factory = $shop->strategy_factory;

        if (!$factory) {
            $factory = Shop::PARSE_STRATEGY_DEFAULT;
        }

        if (!array_key_exists($factory, self::$factoryStrategyMap)) {
            throw new Exception('Unknown parsing factory');
        }

        $factoryClass = self::$factoryStrategyMap[$factory];

        return new $factoryClass($fileImport, $logger);
    }
}
