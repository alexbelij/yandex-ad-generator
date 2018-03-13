<?php

namespace app\assets;

use yii\web\AssetBundle;
use yii\web\View;

/**
 * Class GeneratorAsset
 * @package app\assets
 * @author Denis Dyadyun <sysadm85@gmail.com>
 */
class GeneratorAsset extends AssetBundle
{
    public $sourcePath = '@app/assets/resource';
    
    public $js = [
        'js/generator.js'
    ];

    public $depends = [
        'app\assets\AppAsset'
    ];

    public $jsOptions = [
        'position' => View::POS_END
    ];
}