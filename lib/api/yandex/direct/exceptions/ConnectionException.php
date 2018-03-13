<?php

namespace app\lib\api\yandex\direct\exceptions;

/**
 * Class ConnectionException
 * @package app\lib\api\yandex\direct\exceptions
 */
class ConnectionException extends YandexException
{
    const TOKEN_NOT_FOUND = 53;

    /**
     * @var array
     */
    protected $requestData = [];

    /**
     * @return array
     */
    public function getRequestData()
    {
        return $this->requestData;
    }

    /**
     * @param array $requestData
     * @return $this
     */
    public function setRequestData($requestData)
    {
        $this->requestData = $requestData;
        return $this;
    }
}
