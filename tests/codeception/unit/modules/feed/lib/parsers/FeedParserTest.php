<?php

namespace tests\codeception\unit\modules\feed\lib\parsers;

use app\helpers\ArrayHelper;
use app\modules\feed\lib\parsers\FeedParser;
use app\modules\feed\models\Feed;
use app\modules\feed\models\FeedRedirect;
use tests\codeception\unit\fixtures\suits\common\FeedRedirectFixture;
use yii\codeception\TestCase;

/**
 * Class TagParserTest
 * @package tests\codeception\unit\modules\feed\lib\parsers
 */
class FeedParserTest extends TestCase
{
    /**
     * @var FeedParser
     */
    protected $tagParser;

    /**
     * @inheritDoc
     */
    public function fixtures()
    {
        return [
            FeedRedirectFixture::class,
        ];
    }

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();

        $feed = new Feed([
            'subid' => 'subid=[category]&subid1=[brand]&subid2=[model]&subid3=[price]'
        ]);
        $this->tagParser = new FeedParser($feed);
    }

    /**
     * Тестирование замены ссылок в фиде
     */
    public function testParseFeed()
    {
        $fh = fopen(\Yii::getAlias('@tests/codeception/_support/files/feeds/test1.xml'), 'r');
        $output = '';
        while ($str = fgets($fh)) {
            $output .= $this->tagParser->process($str);
        }

        $feedRedirects = FeedRedirect::find()->all();

        $this->assertCount(6, $feedRedirects);

        $feedRedirects = ArrayHelper::index($feedRedirects, 'hash_url');

        foreach ($this->getExpectedData() as $hash => $expectedUrl) {
            $this->assertNotEmpty($feedRedirects[$hash]);
            $this->assertEquals($expectedUrl, $feedRedirects[$hash]->target_url);
        }
    }

    /**
     * @return array
     */
    protected function getExpectedData()
    {
        return [
            'c20a1b0d88fb7d20d5ae17a543e5b78b' => 'http://www.mebelion.ru/catalog/LST-4524-01.html?utm_source=actionpay&amp;utm_medium=cpa&amp;utm_campaign=xml_svet&subid=Офисные&subid1=Lussole&subid2=Настольная лампа офисная Warshawa LST-4524-01&subid3=910',
            '424a2cde473050183b1f65faf4e41bc5' => 'http://www.mebelion.ru/catalog/ID_821_5PF-LEDWhitegold.html?utm_source=actionpay&amp;utm_medium=cpa&amp;utm_campaign=xml_svet&subid=Потолочные люстры&subid1=IDLamp&subid2=Потолочная люстра 821 821/5PF-LEDWhitegold&subid3=9990',
            'ddc398ea6e634c4498bf9527a12730d6' => 'http://www.mebelion.ru/catalog/LS_807012.html?utm_source=actionpay&amp;utm_medium=cpa&amp;utm_campaign=xml_svet&subid=Подвесные люстры&subid1=Lightstar&subid2=Подвесной светильник Punto 807012&subid3=5889'
        ];
    }
}
