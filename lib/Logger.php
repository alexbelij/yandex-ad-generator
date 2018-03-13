<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 30.09.16
 * Time: 18:57
 */

namespace app\lib;

use app\components\LoggerInterface;

/**
 * Class Logger
 * @package app\lib
 */
class Logger implements LoggerInterface
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
    public function log($msg)
    {
        fwrite($this->fh, sprintf("%s\t%s\r\n", date('d.m.Y h:i:s'), $msg));
    }

}
