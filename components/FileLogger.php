<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 09.04.16
 * Time: 17:48
 */

namespace app\components;

/**
 * Class ConsoleLogger
 * @package app\components
 */
class FileLogger implements LoggerInterface
{
    /**
     * @var resource
     */
    private $fh;

    /**
     * @var string
     */
    private $fileName;

    /**
     * @var string
     */
    private $path;

    /**
     * ConsoleLogger constructor.
     * @param string $filename
     * @param string|null $path
     */
    public function __construct($filename, $path = null)
    {
        $this->fileName = $filename;
        if (!$path) {
            $path = \Yii::getAlias("@app/runtime/logs/");
        }
        $this->path = $path;
        $this->fh = fopen($this->getFileName(), 'a');
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param string $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * @inheritDoc
     */
    public function log($msg)
    {
        $str = sprintf("%s\t%s\r\n", date('d.m.Y h:i:s'), $msg);
        fwrite($this->fh, $str);
        echo $str;
    }

    /**
     * @return bool|string
     */
    public function getFileName()
    {
        return rtrim($this->path, '/') . '/' . $this->fileName;
    }

    /**
     * @inheritDoc
     */
    function __destruct()
    {
        fclose($this->fh);
    }
}
