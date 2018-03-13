<?php

namespace tests\codeception\unit\lib\services;

use app\lib\api\shop\models\ExtProduct;
use app\models\AdTemplate;
use tests\codeception\fakes\FakeVariationGeneratorService;
use yii\base\DynamicModel;
use yii\codeception\TestCase;

/**
 * Тест генератора вариаций
 *
 * Class VariationGeneratorServiceTest
 * @package tests\codeception\unit\lib\services
 * @author Denis Dyadyun <sysadm85@gmail.com>
 */
class VariationGeneratorServiceTest extends TestCase
{
    /**
     * Успешная генерация ключевиков
     */
    public function testSuccessGenerateKeywordsKef()
    {
        $product = new ExtProduct();
        $product->title = 'C5';

        $fakeGeneratorService = new FakeVariationGeneratorService();
        $fakeGeneratorService->brandVariations = ['KEF'];
        $fakeGeneratorService->categoryVariations = ['Напольная акустика'];
        $fakeGeneratorService->templates = [
            new AdTemplate([
                'title' => '[title]!',
                'message' => 'Всего за [price] р. Заказывайте у экспертов! Доставка 0р.'
            ])
        ];
        $result = $fakeGeneratorService->generate($product);

        $this->assertNotEmpty($result);
        $this->assertArrayHasKey(0, $result);
        $this->assertArrayHasKey('keywords', $result[0]);
        $this->assertFalse(
            in_array('Напольная акустика KEF +C5 +C', $result[0]['keywords']),
            'Неверное значение ключевика'
        );
        $this->assertCount(6, $result[0]['keywords']);
    }

    public function testSuccessGenerateKeywordsOrfey()
    {
        $product = new ExtProduct();
        $product->title = 'Орфей-22';

        $fakeGeneratorService = new FakeVariationGeneratorService();
        $fakeGeneratorService->brandVariations = ['Витра-привет'];
        $fakeGeneratorService->categoryVariations = ['Обеденные столы'];
        $fakeGeneratorService->templates = [
            new AdTemplate([
                'title' => '[title]!',
                'message' => 'Всего за [price] р. Заказывайте у экспертов! Доставка 0р.'
            ])
        ];
        $result = $fakeGeneratorService->generate($product);

        $this->assertNotEmpty($result);
        $this->assertArrayHasKey(0, $result);
        $this->assertArrayHasKey('keywords', $result[0]);
        $this->assertCount(8, $result[0]['keywords']);
        $this->assertTrue(strpos($result[0]['keywords'][0], 'Витра привет') !== false);
    }

    public function testLimitLengthKeywordPhrase()
    {
        $product = new ExtProduct();
        $product->title = 'T105 System';

        $fakeGeneratorService = new FakeVariationGeneratorService();
        $fakeGeneratorService->brandVariations = ['KEF'];
        $fakeGeneratorService->categoryVariations = ['Домашние кинотеатры 5.1'];
        $fakeGeneratorService->templates = [
            new AdTemplate([
                'title' => '[title]!',
                'message' => 'Всего за [price] р. Заказывайте у экспертов! Доставка 0р.'
            ])
        ];
        $result = $fakeGeneratorService->generate($product);

        $this->assertNotEmpty($result);
        $this->assertArrayHasKey(0, $result);
        $this->assertArrayHasKey('keywords', $result[0]);
        $this->assertFalse(
            in_array('Домашние кинотеатры +5.1 KEF +T +105 System', $result[0]['keywords']),
            'Неверное значение ключевика'
        );

        $this->assertCount(14, $result[0]['keywords']);
    }

    public function testGenerateOnkyoKeywords()
    {
        $product = new ExtProduct();
        $product->title = 'TX-SR 343';

        $fakeGeneratorService = new FakeVariationGeneratorService();
        $fakeGeneratorService->brandVariations = ['Onkyo'];
        $fakeGeneratorService->categoryVariations = ['5.1 ресиверы'];
        $fakeGeneratorService->templates = [
            new AdTemplate([
                'title' => '[brand] [title]!',
                'message' => 'Всего за [price] руб.! Доставка по России! Скидки в Кинаш Спорт до 70%!'
            ])
        ];
        $result = $fakeGeneratorService->generate($product);

        $this->assertNotEmpty($result);
        $this->assertArrayHasKey(0, $result);
        $this->assertArrayHasKey('keywords', $result[0]);
        $this->assertFalse(in_array('Onkyo TXSR +343', $result[0]['keywords']));
        $this->assertTrue(in_array('Onkyo +TX +SR343', $result[0]['keywords']));

        $this->assertCount(14, $result[0]['keywords']);
    }

    public function testGenerateLumbamedKeywords()
    {
        $product = new ExtProduct();
        $product->title = 'LUMBAMED STABIL (32 см)';

        $fakeGeneratorService = new FakeVariationGeneratorService();
        $fakeGeneratorService->brandVariations = ['Medi'];
        $fakeGeneratorService->categoryVariations = ['Для спины'];
        $fakeGeneratorService->templates = [
            new AdTemplate([
                'title' => '[brand] [title]!',
                'message' => 'Всего за [price] руб.! Доставка по России! Скидки в Кинаш Спорт до 70%!'
            ])
        ];
        $result = $fakeGeneratorService->generate($product);

        $this->assertNotEmpty($result);
        $this->assertArrayHasKey(0, $result);
        $this->assertArrayHasKey('keywords', $result[0]);
        $this->assertTrue(in_array('Medi LUMBAMED STABIL', $result[0]['keywords']));
        $this->assertTrue(in_array('Medi LUMBAMED', $result[0]['keywords']));

        $this->assertCount(30, $result[0]['keywords']);
    }
}
