<?php

namespace tests\codeception\_support\lib\import\yml\strategies\defaultStrategy;

use app\components\LoggerInterface;
use app\components\LoggerStub;
use app\lib\import\yml\CategoriesTree;
use app\lib\import\yml\strategies\categoryBrandStrategy\OfferTagParser;
use app\lib\import\yml\strategies\entity\Offer;
use app\models\FileImport;

/**
 * Class OfferTagParserTestable
 * @package tests\codeception\_support\lib\import\yml\strategies\defaultStrategy
 */
class OfferTagParserTestable extends OfferTagParser
{
    /**
     * @var Offer
     */
    protected $offer;

    /**
     * OfferTagParserTestable constructor.
     * @param FileImport|null $fileImport
     * @param LoggerInterface|null $logger
     */
    public function __construct(FileImport $fileImport = null, LoggerInterface $logger = null)
    {
        $this->fileImport = $fileImport;
        if (!$logger) {
            $logger = new LoggerStub();
        }
        $this->shop = $fileImport->shop;

        $this->logger = $logger;
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param FileImport $fileImport
     */
    public function setFileImport(FileImport $fileImport)
    {
        $this->fileImport = $fileImport;
    }

    /**
     * @return \app\models\ExternalProduct
     */
    public function getExternalProductModel()
    {
        return $this->externalProduct;
    }

    /**
     * Переопределение дерева категорий
     *
     * @param CategoriesTree $categoriesTree
     * @return $this
     */
    public function setCategoriesTree(CategoriesTree $categoriesTree)
    {
        $this->categoriesTree = $categoriesTree;
        return $this;
    }

    /**
     * Подмена резкльтата парсинга товарного предложения
     *
     * @param array $attributes
     * @return $this
     */
    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;
        return $this;
    }

    /**
     * @param Offer $offer
     * @return $this
     */
    public function setOffer(Offer $offer)
    {
        $this->offer = $offer;
        return $this;
    }

    /**
     * @return Offer
     */
    public function getOffer()
    {
        return $this->offer;
    }
}