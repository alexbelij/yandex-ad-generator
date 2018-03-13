<?php

namespace app\components;

/**
 * Interface LoggerInterface
 * @package app\components
 */
interface LoggerInterface
{
    /**
     * @param string $msg
     * @return mixed
     */
    public function log($msg);
}
