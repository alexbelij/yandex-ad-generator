<?php

namespace app\assets;

use yii\web\AssetBundle;

/**
 * Class BootstrapToggleAsset
 * @package app\assets
 */
class BootstrapToggleAsset extends AssetBundle
{
    public $sourcePath = '@app/assets/resource';

    public $js = [
        'bootstrap-toggle/js/bootstrap-toggle.js'
    ];

    public $css = [
        'bootstrap-toggle/css/bootstrap-toggle.css'
    ];
}