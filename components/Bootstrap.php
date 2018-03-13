<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 05.10.16
 * Time: 19:53
 */

namespace app\components;

use app\events\listeners\EventListenerInterface;
use app\events\listeners\SuccessImportFileListener;
use yii\base\BootstrapInterface;
use yii\base\Event;

/**
 * Class Bootstrap
 * @package app\components
 */
class Bootstrap implements BootstrapInterface
{
    /**
     * @inheritDoc
     */
    public function bootstrap($app)
    {
        $this->registerListeners();
    }

    /**
     * Регистрация обработчиков событий
     */
    protected function registerListeners()
    {
        foreach ($this->getListeners() as $listenerClass) {
            /** @var EventListenerInterface $listener */
            $listener = new $listenerClass();
            foreach ($listener->getEvents() as $eventName => $triggers) {
                \Yii::$app->on($eventName, function (Event $event) use ($triggers, $listener) {
                    foreach ((array) $triggers as $trigger) {
                        call_user_func([$listener, $trigger], $event);
                    }
                });
            }
        }
    }

    /**
     * @return array
     */
    protected function getListeners()
    {
        return [
            SuccessImportFileListener::className()
        ];
    }
}
