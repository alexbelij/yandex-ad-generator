<?php

namespace tests\codeception\unit\lib\services;

use app\lib\services\AdService;
use yii\codeception\TestCase;

/**
 * Тест сервиса работы с объявлениями
 *
 * Class AdServiceTest
 * @package tests\codeception\unit\lib\services
 */
class AdServiceTest extends TestCase
{
    /**
     * @param $source
     * @param $expect
     *
     * @dataProvider displayUrlPathProvider
     */
    public function testGetDisplayUrlPath($source, $expect)
    {
        //arrange
        $adServiceClass  = new \ReflectionClass(AdService::class);
        $method = $adServiceClass->getMethod('getDisplayUrlPath');
        $method->setAccessible(true);

        $adService = $this->getMockBuilder(AdService::class)
            ->disableOriginalConstructor()
            ->setMethods(['getDisplayUrlPath'])
            ->getMock();

        //act
        $val = $method->invokeArgs($adService, [$source]);

        //assert
        $this->assertEquals($expect, $val);
    }

    /**
     * @return array
     */
    public function displayUrlPathProvider()
    {
        return [
            ['Мебель Трия Фиджи ШУ(08)_23#R 1/8', 'ШУ-08-23#R-1/8'],
            ['Mebelson ', 'Mebelson'],
            ['RUSBRAND FS№H3.2/0810', 'FS№H3-2/0810'],
            ['Turin Turinчерный_бордовый', 'Turinчерный-бордовый']
        ];
    }
}
