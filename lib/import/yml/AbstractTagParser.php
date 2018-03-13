<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 24.08.16
 * Time: 10:33
 */

namespace app\lib\import\yml;
use app\components\LoggerInterface;
use app\models\FileImport;

/**
 * Class AbstractTagParser
 * @package app\lib\import\yml
 */
abstract class AbstractTagParser
{
    /**
     * @var FileImport
     */
    protected $fileImport;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * AbstractTagParser constructor.
     * @param FileImport $fileImport
     * @param LoggerInterface $logger
     */
    public function __construct(FileImport $fileImport, LoggerInterface $logger)
    {
        $this->fileImport = $fileImport;
        $this->logger = $logger;
    }

    /**
     * @param string $tagName
     * @param array $attributes
     * @return mixed
     */
    abstract public function parseAttributes($tagName, $attributes);

    /**
     * @param string $tagName
     * @param mixed $data
     * @return mixed
     */
    abstract public function parseCharacters($tagName, $data);

    /**
     * @param string $tagName
     */
    public function end($tagName)
    {
        //pass
    }
}
