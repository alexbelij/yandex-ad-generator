<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 05.10.16
 * Time: 19:59
 */

namespace app\events\listeners;

/**
 * Interface EventHandlerInterface
 * @package app\events\handlers
 */
interface EventListenerInterface
{
    /**
     * Возвращает список событий
     *
     * @return array
     */
    public function getEvents();
}
