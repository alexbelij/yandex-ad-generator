<?php

namespace app\modules\feed\assets;

use app\assets\AppAsset;
use yii\web\AssetBundle;

/**
 * Class FeedAsset
 * @package app\modules\feed\assets
 */
class FeedAsset extends AssetBundle
{
    public $sourcePath = '@feed/assets/resource';

    public $js = [
        'js/feed.js'
    ];

    public $depends = [
        AppAsset::class,
    ];
}
