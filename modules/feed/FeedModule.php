<?php

namespace app\modules\feed;

use yii\base\Module;
use yii\console\Application;

/**
 * Class FeedModule
 * @package app\modules\feed
 */
class FeedModule extends Module
{
    /**
     * @inheritDoc
     */
    public function init()
    {
        parent::init();

        if (\Yii::$app instanceof Application) {
            $this->controllerNamespace = 'app\modules\feed\commands';
        }

        $this->defaultRoute = 'index';
        $this->layout = 'main';
    }
}
