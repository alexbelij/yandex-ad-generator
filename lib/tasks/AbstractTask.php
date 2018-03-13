<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 10.04.16
 * Time: 11:27
 */

namespace app\lib\tasks;

use app\components\FileLogger;
use app\components\LoggerInterface;
use app\models\TaskQueue;

/**
 * Class AbstractTask
 * @package app\lib\tasks
 */
abstract class AbstractTask implements TaskInterface
{
    /**
     * @var TaskQueue
     */
    protected $task;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * BaseOperation constructor.
     * @param TaskQueue $task
     */
    public function __construct(TaskQueue $task)
    {
        $this->task = $task;
        $this->init();
    }

    /**
     * Действия выполняемые при инициализации класса
     */
    protected function init()
    {
        $this->task->log_file = $this->getLogFileName();
        $this->task->save();
    }

    /**
     * @return bool|string
     */
    public static function getTaskLogDir()
    {
        return \Yii::getAlias('@app/runtime/logs/tasks/');
    }

    /**
     * @return null|string
     */
    protected function getLogFileName()
    {
        return null;
    }

    /**
     * @return FileLogger|LoggerInterface
     * @throws TaskException
     */
    protected function getLogger()
    {
        if (is_null($this->logger)) {
            $logDir = self::getTaskLogDir();
            if (!file_exists($logDir) && !mkdir($logDir, 0777, true)) {
                throw new TaskException('Не удалось создать директорию для логирования');
            }
            $this->logger = new FileLogger($this->getLogFileName(), $logDir);
        }

        return $this->logger;
    }
}
