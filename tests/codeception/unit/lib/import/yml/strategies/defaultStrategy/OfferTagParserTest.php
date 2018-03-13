<?php

namespace tests\codeception\unit\lib\import\yml\strategies\defaultStrategy;

use app\lib\import\yml\CategoriesTree;
use app\lib\import\yml\strategies\entity\Category;
use app\lib\import\yml\strategies\entity\Offer;
use app\models\ExternalCategory;
use app\models\FileImport;
use tests\codeception\_support\lib\import\yml\strategies\defaultStrategy\OfferTagParserTestable;
use tests\codeception\unit\fixtures\suits\common\AccountFixture;
use tests\codeception\unit\fixtures\suits\common\ShopFixture;
use tests\codeception\unit\fixtures\suits\import\ExternalBrandFixture;
use tests\codeception\unit\fixtures\suits\import\ExternalCategoryFixture;
use tests\codeception\unit\fixtures\suits\import\VariationFixture;
use tests\codeception\unit\fixtures\suits\import\VariationItemFixture;
use tests\codeception\unit\fixtures\suits\import\WordExceptionFixture;
use yii\codeception\TestCase;
use yii\test\InitDbFixture;

/**
 * Тест парсера товаров
 *
 * Class OfferTagParserTest
 * @package tests\codeception\unit\lib\import\yml\strategies\defaultStrategy
 */
class OfferTagParserTest extends TestCase
{
    /**
     * @inheritDoc
     */
    public function fixtures()
    {
        return [
            InitDbFixture::class,
            AccountFixture::class,
            ShopFixture::class,
            ExternalBrandFixture::class,
            ExternalCategoryFixture::class,
            WordExceptionFixture::class,
            VariationFixture::class,
            VariationItemFixture::class,
        ];
    }

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();
        \phpMorphy_Util_MbstringOverloadFixer::fix();
    }


    /**
     * Тестирование вырезки лишнего из модели
     *
     * @dataProvider dataModelProvider
     */
    public function testParse_Success($model, $title)
    {
        //arrange
        $offerTagParser = $this->getOfferTagParser(
            new Offer([
                'id' => 'TET_leader_blue',
                'isAvailable' => true,
                'url' => 'https://ad.admitad.com/g/b2f204e91ed4d5120fe4da0f3ff6fc/?i=5&amp;ulp=http%3A%2F%2Fwww.mebelion.ru%2Fcatalog%2FTET_leader_blue.html',
                'name' => 'Кресло компьютерное Tetchair',
                'typePrefix' => 'Кресло компьютерное',
                'model' => $model,
                'picture' => 'http://photo.mebelion.ru/images/46d5e3789afaf6af3e5bd9565c943462.jpg',
                'price' => '3890',
                'vendor' => 'Tetchair',
                'categoryId' => 123768
            ]),
            1
        );

        //act
        $offerTagParser->end('offer');

        //assert
        $externalProduct = $offerTagParser->getExternalProductModel();

        $this->assertEquals($title, $externalProduct->title);
    }


    public function testParseBrandFromCategory()
    {
        //arrange
        $offerTagParser = $this->getOfferTagParser(
            new Offer([
                'id' => '1537',
                'isAvailable' => true,
                'url' => 'https://sewcity.ru/gladilnye-doski/comfort-vapo-2/gladilnaya-doska-comfort-vapo-comfort-vapo.html',
                'name' => 'Гладильная доска Comfort Vapo',
                'picture' => 'http://photo.mebelion.ru/images/46d5e3789afaf6af3e5bd9565c943462.jpg',
                'price' => '3890',
                'categoryId' => 203
            ]),
            2
        );

        //act
        $offerTagParser->end('offer');

        //assert
        $externalProduct = $offerTagParser->getExternalProductModel();

        $this->assertEquals(2596, $externalProduct->category_id);
        $this->assertEquals(275, $externalProduct->brand_id);
        $this->assertEquals('', $externalProduct->title);
    }

    public function testParseBrandFromCategory2()
    {
        //arrange
        $offerTagParser = $this->getOfferTagParser(
            new Offer([
                'id' => 955,
                'isAvailable' => true,
                'url' => 'https://sewcity.ru/parogeneratory-s-utyugom/aeg/parogenerator-utyugom-aeg-558-aeg-5558-aeg-558.html',
                'name' => 'Парогенератор утюгом AEG 558 / AEG 5558',
                'picture' => 'http://photo.mebelion.ru/images/46d5e3789afaf6af3e5bd9565c943462.jpg',
                'price' => '3890',
                'categoryId' => 190
            ]),
            2
        );

        //act
        $offerTagParser->end('offer');

        //assert
        $externalProduct = $offerTagParser->getExternalProductModel();

        $this->assertEquals(2623, $externalProduct->category_id);
        $this->assertEquals(276, $externalProduct->brand_id);
        $this->assertEquals('Парогенератор утюгом 558/5558', $externalProduct->title);
    }

    public function testParseBrandFromCategory3()
    {
        //arrange
        $offerTagParser = $this->getOfferTagParser(
            new Offer([
                'id' => 1050,
                'isAvailable' => true,
                'url' => 'https://sewcity.ru/parogeneratory-s-utyugom/aeg/parogenerator-utyugom-aeg-558-aeg-5558-aeg-558.html',
                'name' => 'Швейно-Вышивальная машина Husqvarna Designer Topaz 30',
                'picture' => 'http://photo.mebelion.ru/images/46d5e3789afaf6af3e5bd9565c943462.jpg',
                'price' => '3890',
                'categoryId' => 191
            ]),
            2
        );

        //act
        $offerTagParser->end('offer');

        //assert
        $externalProduct = $offerTagParser->getExternalProductModel();

        $this->assertEquals(2625, $externalProduct->category_id);
        $this->assertEquals(277, $externalProduct->brand_id);
        $this->assertEquals('Designer Topaz 30', $externalProduct->title);
    }

    public function testParseBrandFromCategory4()
    {
        //arrange
        $offerTagParser = $this->getOfferTagParser(
            new Offer([
                'id' => 1052,
                'isAvailable' => true,
                'url' => 'https://sewcity.ru/parogeneratory-s-utyugom/aeg/parogenerator-utyugom-aeg-558-aeg-5558-aeg-558.html',
                'name' => 'Отпариватель для одежды  Elinastar Vapor с куклой для длиных вещей 800',
                'picture' => 'http://photo.mebelion.ru/images/46d5e3789afaf6af3e5bd9565c943462.jpg',
                'price' => '3890',
                'categoryId' => 192
            ]),
            2
        );

        //act
        $offerTagParser->end('offer');

        //assert
        $externalProduct = $offerTagParser->getExternalProductModel();

        $this->assertEquals(2627, $externalProduct->category_id);
        $this->assertEquals(278, $externalProduct->brand_id);
        $this->assertEquals('Vapor 800', $externalProduct->title);
    }

    /**
     * @return array
     */
    public function dataModelProvider()
    {
        return [
            ['Кресло компьютерное Leader синее/серое/бронза античная глянцевый/дуб белфорт с рисунком/ 2/4/5 /лён/ ,с', 'Leader 2/4/5 лён'],
            ['217 S дуб в, красноту', '217 S'],
            ['3696 дуб, черный', '3696'],
            ['Parma коричневый_бежевый', 'Parma']
        ];
    }

    /**
     * @param Offer $offer
     * @param int $shopId
     * @return OfferTagParserTestable
     */
    protected function getOfferTagParser(Offer $offer, $shopId)
    {
        $offerTagParser = new OfferTagParserTestable(new FileImport(['shop_id' => $shopId]));
        $offerTagParser->setOffer($offer);
        $offerTagParser->setCategoriesTree($this->getCategoriesTree($shopId));

        return $offerTagParser;
    }

    /**
     * @param int $shopId
     * @return CategoriesTree
     */
    protected function getCategoriesTree($shopId)
    {
        /** @var ExternalCategory[] $categoriesModels */
        $categoriesModels = ExternalCategory::find()
            ->andWhere(['shop_id' => $shopId])
            ->all();

        $categories = [];
        foreach ($categoriesModels as $category) {
            $categories[] = new Category([
                'id' => $category->outer_id,
                'title' => $category->title,
                'parentId' => $category->parent_id
            ]);
        }

        return new CategoriesTree($categories);
    }
}
