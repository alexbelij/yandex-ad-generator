<?php

namespace app\lib\api\yandex\direct\events;

use app\lib\api\yandex\events\RequestEvent;
use Zend\Http\Response;

/**
 * Class UnitUpdateListener
 * @package app\lib\api\yandex\direct\events
 * @author Denis Dyadyun <sysadm85@gmail.com>
 */
class UnitUpdateListener
{
    /**
     * @param RequestEvent $event
     */
    public function update(RequestEvent $event)
    {
        /** @var Response $response */
        $response = $event->response;
        $account = $event->account;

        $headers = $response->getHeaders()->toArray();

        if (array_key_exists('Units', $headers)) {
            $units = $headers['Units'];
            $account->units = substr($units, strpos($units, '/') + 1);
            $account->save();
        }

    }
}
