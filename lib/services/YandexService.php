<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 09.04.16
 * Time: 22:02
 */

namespace app\lib\services;

use app\components\LoggerInterface;
use app\lib\api\yandex\direct\exceptions\YandexException;
use app\lib\api\yandex\direct\query\ChangeResult;

class YandexService
{
    const MAX_CHUNK_SIZE = 1000;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @param LoggerInterface $logger
     * @return $this
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * @param $msg
     */
    protected function log($msg)
    {
        if ($this->logger) {
            $this->logger->log($msg);
        }
    }

    /**
     * @param ChangeResult $result
     * @throws YandexException
     */
    public function throwExceptionFromResult(ChangeResult $result)
    {
        $error = $result->firstError();

        throw (new YandexException($error->errorInfo(), $error->getCode()))->setDetails($error->getDetails());
    }
}
