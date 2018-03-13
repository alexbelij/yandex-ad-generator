<?php

namespace app\lib;

use Psr\Log\LoggerInterface;

/**
 * Class ConsoleLogger
 * @package app\lib
 */
class ConsoleLogger implements LoggerInterface
{
    /**
     * @var resource
     */
    private $fh;

    /**
     * Logger constructor.
     */
    public function __construct()
    {
        $this->fh = fopen('php://stdout', 'w');
    }

    /**
     * @inheritDoc
     */
    public function emergency($message, array $context = array())
    {
        $this->log('emergency', $message, $context);
    }

    /**
     * @inheritDoc
     */
    public function alert($message, array $context = array())
    {
        $this->log('alert', $message, $context);
    }

    /**
     * @inheritDoc
     */
    public function critical($message, array $context = array())
    {
        $this->log('critical', $message, $context);
    }

    /**
     * @inheritDoc
     */
    public function error($message, array $context = array())
    {
        $this->log('error', $message, $context);
    }

    /**
     * @inheritDoc
     */
    public function warning($message, array $context = array())
    {
        $this->log('warning', $message, $context);
    }

    /**
     * @inheritDoc
     */
    public function notice($message, array $context = array())
    {
        $this->log('notice', $message, $context);
    }

    /**
     * @inheritDoc
     */
    public function info($message, array $context = array())
    {
        $this->log('info', $message, $context);
    }

    /**
     * @inheritDoc
     */
    public function debug($message, array $context = array())
    {
        $this->log('debug', $message, $context);
    }

    /**
     * @inheritDoc
     */
    public function log($level, $message, array $context = array())
    {
        fwrite($this->fh, sprintf("%s\t%s\r\n", date('d.m.Y h:i:s'), $message));
    }

}