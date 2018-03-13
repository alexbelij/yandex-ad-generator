<?php

namespace app\modules\bidManager;

use yii\base\Module;
use yii\console\Application;

/**
 * Class Module
 * @package app\modules\bidManager
 */
class BidManagerModule extends Module
{
    /**
     * @inheritDoc
     */
    public function init()
    {
        parent::init();

        if (\Yii::$app instanceof Application) {
            $this->controllerNamespace = 'app\modules\bidManager\commands';
        }

        $this->defaultRoute = 'index';
        $this->layout = 'main';
    }
}
