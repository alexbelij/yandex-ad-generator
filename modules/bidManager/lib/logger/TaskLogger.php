<?php

namespace app\modules\bidManager\lib\logger;

use app\helpers\ArrayHelper;
use app\modules\bidManager\models\Task;
use app\modules\bidManager\models\TaskLog;
use Psr\Log\LoggerInterface;
use yii\base\Exception;

/**
 * Class TaskLogger
 * @package app\modules\bidManager\lib\logger
 */
class TaskLogger implements LoggerInterface
{
    /**
     * @var Task
     */
    protected $task;

    /**
     * TaskLogger constructor.
     * @param Task $task
     */
    public function __construct(Task $task)
    {
        $this->task = $task;
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
        $taskLog = new TaskLog([
            'task_id' => $this->task->id,
            'message' => $message,
            'level' => $level,
            'context' => json_encode($context)
        ]);

        if (!$taskLog->save()) {
            throw new Exception('Не удалось создать запись в логе: ' . ArrayHelper::first($taskLog->getFirstErrors()));
        }
    }
}
