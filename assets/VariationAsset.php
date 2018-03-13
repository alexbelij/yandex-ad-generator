<?php

namespace app\assets;

use yii\web\AssetBundle;

/**
 * Class VariationAsset
 * @package app\assets
 */
class VariationAsset extends AssetBundle
{
    public $sourcePath = '@app/assets/resource';

    public $js = [
        'js/variationController.js'
    ];
}
