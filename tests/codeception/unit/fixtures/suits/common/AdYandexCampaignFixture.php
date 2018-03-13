<?php

namespace tests\codeception\unit\fixtures\suits\common;

use app\models\AdYandexCampaign;
use tests\codeception\unit\fixtures\ActiveFixture;

/**
 * Class AdYandexCampaignFixture
 * @package tests\codeception\unit\fixtures\suits\common
 */
class AdYandexCampaignFixture extends ActiveFixture
{
    public $modelClass = AdYandexCampaign::class;
}
