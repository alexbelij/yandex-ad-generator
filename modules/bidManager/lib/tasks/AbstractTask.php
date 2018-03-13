<?php

namespace app\modules\bidManager\lib\tasks;

use app\modules\bidManager\lib\logger\TaskLogger;
use app\modules\bidManager\models\Task;
use Psr\Log\LoggerInterface;

/**
 * Class AbstractTask
 * @package app\modules\bidManager\lib\tasks
 */
abstract class AbstractTask
{
    /**
     * @var Task
     */
    protected $task;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * AbstractTask constructor.
     * @param Task $task
     */
    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    /**
     * @return TaskLogger
     */
    public function getLogger()
    {
        if (is_null($this->logger)) {
            $this->logger = new TaskLogger($this->task);
        }

        return $this->logger;
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

    /**
     * Выполнение задачи
     *
     * @param array $params
     * @return mixed
     */
    abstract public function execute($params = []);

    /**
     * @return mixed
     */
    abstract public function getName();
}
