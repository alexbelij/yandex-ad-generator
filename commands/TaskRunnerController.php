<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 27.03.16
 * Time: 19:11
 */

namespace app\commands;

use app\lib\reports\ReportInterface;
use app\lib\tasks\TaskException;
use app\lib\tasks\TaskInterface;
use app\models\TaskQueue;
use yii\base\ErrorException;
use yii\base\Exception;
use yii\console\Controller;
use yii\helpers\Inflector;

/**
 * Class TaskRunnerController
 * @package app\commands
 */
class TaskRunnerController extends Controller
{
    /**
     * Выполнение тасков
     * @throws Exception
     */
    public function actionIndex()
    {
        while (null !== ($task = TaskQueue::getNextTaskForRun())) {
            $taskCommand = $this->createTaskCommand($task);
            try {
                $task->markRun();
                $taskCommand->execute();
                $task->markCompleted();
            } catch (Exception $e) {
                $task->markError($e);
            } catch (\Exception $e) {
                $task->markError($e);
            }

            $context = $task->getContext();
            if (!empty($context['reportName'])) {
                $this->getReport($context['reportName'])->send(['task' => $task]);
            }
        }
    }

    /**
     * @param string $reportName
     * @return ReportInterface
     * @throws TaskException
     */
    protected function getReport($reportName)
    {
        $className = 'app\\lib\\reports\\' . Inflector::camelize($reportName);

        if (!$className) {
            throw new TaskException('Отчет - "' . $className . '" не найден');
        }

        return new $className;
    }

    /**
     * Возвращает операцию для запуска
     *
     * @param TaskQueue $task
     * @return TaskInterface
     * @throws Exception
     */
    protected function createTaskCommand(TaskQueue $task)
    {
        $operation = $task->operation;
        $operation = Inflector::camelize($operation);
        $taskClass = 'app\lib\tasks\\' . $operation . 'Task';

        if (!class_exists($taskClass)) {
            throw new Exception("Operation - '$operation' not found.");
        }

        return new $taskClass($task);
    }
}
