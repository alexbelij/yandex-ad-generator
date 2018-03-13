<?php

namespace app\assets;

use yii\web\AssetBundle;

/**
 * Class CommonAsset
 * @package app\assets
 * @author Denis Dyadyun <sysadm85@gmail.com>
 */
class CommonAsset extends AssetBundle
{
    public $sourcePath = '@app/assets/resource';
    
    public $js = [
        'js/common.js',
        'js/bootbox.min.js',
        'plugins/jquery.inputmask.bundle.js'
    ];

    public $css = [
        'css/common.css'
    ];
    
    public $depends = [
        'app\assets\AppAsset'
    ];
}
