<?php

namespace tests\codeception\unit\fixtures\suits\common;

use app\modules\feed\models\FeedRedirect;
use tests\codeception\unit\fixtures\ActiveFixture;

/**
 * Class FeedRedirectFixture
 * @package tests\codeception\unit\fixtures\suits\common
 */
class FeedRedirectFixture extends ActiveFixture
{
    public $modelClass = FeedRedirect::class;
}
