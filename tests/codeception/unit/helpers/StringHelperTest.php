<?php

namespace tests\codeception\unit\helpers;

use app\helpers\StringHelper;
use yii\codeception\TestCase;

/**
 * Тест хелпера работы со строками
 *
 * Class StringHelperTest
 * @package tests\codeception\unit\helpers
 */
class StringHelperTest extends TestCase
{
    /**
     * @param string $word
     * @param string $expect
     * @param int $length
     * @param bool $fromEnd
     *
     * @dataProvider wordProvider
     */
    public function testTruncateByWords_cutFromEnd($word, $expect, $length, $fromEnd)
    {
        $this->assertEquals($expect, StringHelper::truncateByWords($word, $length, $fromEnd));
    }

    /**
     * @return array
     */
    public function wordProvider()
    {
        return [
            ['Мебель Трия Фиджи ШУ(08)_23R', 'Фиджи ШУ(08)_23R', 20, true],
            ['Мебель Трия Фиджи ШУ(08)_23R', 'Мебель Трия Фиджи', 20, false],
        ];
    }
}
