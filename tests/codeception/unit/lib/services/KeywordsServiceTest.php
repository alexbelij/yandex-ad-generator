<?php

namespace tests\codeception\unit\lib\services;

use app\lib\api\yandex\direct\resources\KeywordsResource;
use app\lib\services\KeywordsService;
use app\models\AdYandexCampaign;
use PHPUnit_Framework_MockObject_MockObject;
use tests\codeception\unit\fixtures\suits\common\AdFixture;
use tests\codeception\unit\fixtures\suits\common\AdYandexCampaignFixture;
use tests\codeception\unit\fixtures\suits\common\ExternalBrandFixture;
use tests\codeception\unit\fixtures\suits\common\ExternalProductFixture;
use tests\codeception\unit\fixtures\suits\common\ProductFixture;
use tests\codeception\unit\fixtures\suits\common\ShopFixture;
use yii\codeception\TestCase;

/**
 * Class KeywordsServiceTest
 * @package tests\codeception\unit\lib\services
 */
class KeywordsServiceTest extends TestCase
{
    /**
     * @inheritDoc
     */
    public function fixtures()
    {
        return [
            ShopFixture::class,
            ExternalBrandFixture::class,
            ExternalProductFixture::class,
            ProductFixture::class,
            AdFixture::class,
            AdYandexCampaignFixture::class
        ];
    }

    public function testUpdateGroup()
    {
        //arrange
        $keywordsResource = $this->getMockBuilder(KeywordsResource::class)
            ->setMethods(['add'])
            ->disableOriginalConstructor()
            ->getMock();

        $keywordsResource
            ->expects($this->any())
            ->method('add')
            ->will($this->returnArgument(0));

        /** @var KeywordsService|PHPUnit_Framework_MockObject_MockObject $keywordsServiceMock */
        $keywordsServiceMock = $this->getMockBuilder(KeywordsService::class)
            ->setConstructorArgs([$keywordsResource])
            ->setMethods(['getKeywordsForCreate'])
            ->getMock();

        $sourceKeywords = [
            'Крепления для акустики Canton Wallmount',
            'Canton your Duo Wallmount цена',
            'Canton Duo'
        ];

        $targetKeywords = [
            'Крепления для акустики "Canton" Wallmount',
            '"Canton" your Duo Wallmount цена',
            '"Canton" Duo'
        ];

        $keywordsServiceMock
            ->expects($this->once())
            ->method('getKeywordsForCreate')
            ->will($this->returnValue($sourceKeywords));

        $yandexAd = AdYandexCampaign::findOne(1);

        //act
        $resultItems = $keywordsServiceMock->updateGroup($yandexAd, 10);

        //assert
        foreach ($resultItems as $i => $resultData) {
            $this->assertEquals($targetKeywords[$i], $resultData['Keyword']);
        }
    }
}
