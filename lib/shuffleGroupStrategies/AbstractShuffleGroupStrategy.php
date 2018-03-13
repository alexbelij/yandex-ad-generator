<?php

namespace app\lib\shuffleGroupStrategies;

use app\components\LoggerInterface;
use app\lib\api\yandex\direct\Connection;
use app\lib\LoggedInterface;
use app\lib\YandexOperationLogger;
use app\models\AdYandexGroup;
use app\models\Shop;

/**
 * Interface ShuffleGroupStrategyInterface
 */
abstract class AbstractShuffleGroupStrategy
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var YandexOperationLogger
     */
    protected $yandexOperationLogger;

    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var Shop
     */
    protected $shop;

    /**
     * AbstractShuffleGroupStrategy constructor.
     * @param LoggerInterface $logger
     * @param Connection $connection
     * @param Shop $shop
     * @param YandexOperationLogger $yandexOperationLogger
     */
    public function __construct(
        LoggerInterface $logger, Connection $connection, Shop $shop, YandexOperationLogger $yandexOperationLogger
    ) {
        $this->logger = $logger;
        $this->connection = $connection;
        $this->shop = $shop;
        $this->yandexOperationLogger = $yandexOperationLogger;
        $this->init();
    }

    /**
     * Инициализация
     */
    protected function init()
    {

    }

    /**
     * @param AdYandexGroup[] $groups
     * @return mixed
     */
    abstract public function execute($groups);

    /**
     * Логирование операции
     *
     * @param LoggedInterface $model
     * @param $operation
     * @param string $status
     * @param string $message
     */
    protected function logOperation(
        LoggedInterface $model, $operation, $status = 'success', $message = ''
    ) {
        $this->yandexOperationLogger->log($model, $operation, $status, $message);
        $consoleLogMessage = "$status $operation " . $model->getEntityType() . ' ' . $message;
        $this->logger->log($consoleLogMessage);
    }
}
