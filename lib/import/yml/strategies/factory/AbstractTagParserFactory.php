<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 09.10.16
 * Time: 10:54
 */

namespace app\lib\import\yml\strategies\factory;

use app\components\LoggerInterface;
use app\components\LoggerStub;
use app\models\FileImport;

/**
 * Class AbstractParserTagFactory
 * @package app\lib\import\yml\strategies\factory
 */
abstract class AbstractTagParserFactory implements TagParserFactoryInterface
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
     * DefaultStrategyFactory constructor.
     * @param FileImport $fileImport
     * @param LoggerInterface $logger
     */
    public function __construct(FileImport $fileImport, LoggerInterface $logger = null)
    {
        $this->fileImport = $fileImport;
        if (!$logger) {
            $logger = new LoggerStub();
        }
        $this->logger = $logger;
    }

    /**
     * @param LoggerInterface $logger
     * @return $this
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
        return $this;
    }
}
