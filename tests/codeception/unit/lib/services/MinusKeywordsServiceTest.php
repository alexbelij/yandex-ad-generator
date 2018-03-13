<?php

namespace tests\codeception\unit\lib\services;

use app\components\FileLogger;
use app\lib\services\MinusKeywordsService;
use yii\codeception\TestCase;

/**
 * Class MinusKeywordsServiceTest
 * @package tests\codeception\unit\lib\services
 */
class MinusKeywordsServiceTest extends TestCase
{
    public function testExecute()
    {
        //arrange
        $keywords = [
            'влагостойкий гипсокартон кнауф',
            'гипсокартон влагостойкий кнауф 12.5 мм цена',
            'гипсокартон влагостойкий кнауф 2500х1200х 12.5',
            'гипсокартон влагостойкий кнауф 2500х1200х 12.5 мм цена',
            'гипсокартон влагостойкий кнауф 2500х1200х 12.5 цена',
            'гипсокартон кнауф',
            'гипсокартон кнауф 12.5',
            'гипсокартон кнауф 12.5 мм',
            'гипсокартон кнауф 12.5 мм влагостойкий'
        ];

        $loggerMock = $this->createMock(FileLogger::class);

        $minusKeywordsService = new MinusKeywordsService($loggerMock);

        //act
        $result = $minusKeywordsService->execute($keywords);

        //assert
        $expected = [
            'влагостойкий гипсокартон кнауф -12.5 -мм -цена -2500х1200х',
            'гипсокартон влагостойкий кнауф 12.5 мм цена -2500х1200х',
            'гипсокартон влагостойкий кнауф 2500х1200х 12.5 -мм -цена',
            'гипсокартон влагостойкий кнауф 2500х1200х 12.5 цена -мм',
            'гипсокартон кнауф -влагостойкий -12.5 -мм -цена -2500х1200х',
            'гипсокартон кнауф 12.5 -влагостойкий -мм -цена -2500х1200х',
            'гипсокартон кнауф 12.5 мм -влагостойкий -цена -2500х1200х',
            'гипсокартон кнауф 12.5 мм влагостойкий -цена -2500х1200х',
        ];

        $this->assertEquals($expected, array_values($result));
    }
}
