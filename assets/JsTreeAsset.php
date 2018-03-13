<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 04.10.16
 * Time: 18:40
 */

namespace app\assets;

use yii\web\AssetBundle;

class JsTreeAsset extends AssetBundle
{
    public $sourcePath = '@app/assets/resource/jstree';

    public $js = [
        'jstree.min.js'
    ];

    public $css = [
        'themes/default/style.min.css'
    ];
}