<?php

namespace app\assets;

use yii\web\AssetBundle;
use yii\web\YiiAsset;

/**
 * Class ChosenAsset
 * @package app\assets
 * @author Denis Dyadyun <sysadm85@gmail.com>
 */
class ChosenAsset extends AssetBundle
{
    public $sourcePath = '@app/assets/resource/plugins/chosen';

    public $js = [
        'chosen.jquery.min.js'
    ];

    public $css = [
        'chosen.min.css'
    ];

    public $depends = [
        'yii\web\YiiAsset'
    ];
}
