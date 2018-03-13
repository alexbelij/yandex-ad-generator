<?php

namespace app\modules\bidManager\commands;

use app\helpers\ArrayHelper;
use app\lib\ConsoleLogger;
use app\lib\Logger;
use app\lib\PointsCalculator;
use app\modules\bidManager\lib\services\BidUpdaterService;
use app\modules\bidManager\lib\tasks\AbstractTask;
use app\modules\bidManager\lib\tasks\BidUpdateTask;
use app\modules\bidManager\lib\tasks\SyncCampaignTask;
use app\modules\bidManager\lib\tasks\SyncDeltaTask;
use app\modules\bidManager\lib\tasks\SyncFullTask;
use app\modules\bidManager\models\Account;
use app\modules\bidManager\models\Task;
use yii\console\Controller;
use yii\console\Exception;

/**
 * Контроллер управления ставками и синхронизации с директом
 *
 * Class BidController
 * @package app\modules\bidManager\commands
 */
class BidController extends Controller
{
    /**
     * @param null $accountId
     */
    public function actionBidUpdate($accountId = null)
    {
        /** @var Account[] $accounts */
        $accounts = Account::find()
            ->andFilterWhere(['id' => $accountId])
            ->all();

        $bidUpdateService = new BidUpdaterService();

        foreach ($accounts as $account) {
            $bidUpdateService->update($account);
        }
    }

    /**
     * Простой планировщик, ходит по очереди и запускает задачи
     */
    public function actionTaskSchedule()
    {
        while ($task = Task::getNextTaskForRun()) {
            $logFile = $this->getLogFile($task->id);
            $command = 'php ' . \Yii::getAlias('@app') . "/yii bid-manager/bid/task-execute {$task->id} >$logFile 2>&1 &";
            exec($command);
        }
    }

    /**
     * Возвращает имя файла для логирования вывода, если такой директории не существует пытается создать
     *
     * @param int $taskId
     * @return bool|string
     * @throws \Exception
     */
    protected function getLogFile($taskId)
    {
        $logFile = \Yii::getAlias("@app/runtime/bid-manager/logs/{$taskId}_" . date('Y_m_d_H_i_s') . '.log');
        $dir = dirname($logFile);
        if (!file_exists($dir)) {
            if (!mkdir($dir, 0777, true)) {
                throw new \Exception('Ошибка при создании директории для логирования синхронизаций');
            }
        }

        return $logFile;
    }

    /**
     * Выполнение конкретной задачи
     *
     * @param int $id
     * @throws Exception
     */
    public function actionTaskExecute($id)
    {
        $task = Task::findOne($id);
        if (!$task) {
            throw new Exception('Задача не найдена');
        }

        $logger = new Logger();
        $className = $task->task;
        /** @var AbstractTask $instance */
        $instance = new $className($task);

        $logger->log('Запуск задачи: ' . $task->id . ', ' . $task->task);
        try {
            $task->started_at = date('Y-m-d H:i:s');
            $instance->execute();
            $task->status = Task::STATUS_COMPLETED;
            $logger->log('Задача завершена');
        } catch (\Exception $e) {
            $task->status = Task::STATUS_ERROR;
            $task->message = 'File: ' . $e->getFile() . ', line: ' . $e->getLine() . ', code: ' . $e->getCode() .
                ', message: ' . $e->getMessage() . ', trace: ' . $e->getTraceAsString();
            $logger->log($task->message);
        }

        $task->total_points = round(PointsCalculator::getInstance()->getTotal());
        $task->finished_at = date('Y-m-d H:i:s');
        $task->save();
    }

    /**
     * Добавить задачи в очередь
     *
     * @param string $type
     * @param null|int $accountId
     * @param bool $force
     * @throws Exception
     */
    public function actionCreateTask($type, $accountId = null, $force = false)
    {
        $map = [
            'full-sync' => SyncFullTask::class,
            'delta-sync' => SyncDeltaTask::class,
            'campaign-sync' => SyncCampaignTask::class,
            'bid-update' => BidUpdateTask::class,
        ];

        if (!isset($map[$type])) {
            throw new Exception('Неизвестный тип задачи - ' . $type);
        }

        /** @var Account[] $accounts */
        $accounts = Account::find()
            ->andFilterWhere(['id' => $accountId])
            ->all();

        $count = 0;
        $logger = new ConsoleLogger();
        $logger->info('Создаем задачи');
        foreach ($accounts as $account) {
            if ($force || !Task::hasActiveTasks($account->id, $map[$type])) {
                $taskInstance = Task::createNewTask($account->id, $map[$type]);
                if (!$taskInstance || $taskInstance->hasErrors()) {
                    $logger->error(
                        'Ошибка при создании задачи: ' .
                        ($taskInstance ? ArrayHelper::first($taskInstance->getFirstErrors()) : 'неизвестная')
                    );
                } else {
                    $count++;
                    $logger->info('Успешное создание задачи: ' . $taskInstance->id . ' : ' . $taskInstance->task);
                }
            }
        }

        $logger->info('Завершено, создано: ' . $count . ' задач');
    }

    /**
     * Завершение зависших тасков
     */
    public function actionTaskWatchDog()
    {
        /** @var Task[] $tasks */
        $tasks = Task::find()
            ->andWhere(['status' => Task::STATUS_PROCESSING])
            ->andWhere(['<=', 'started_at', date('Y-m-d H:i:s', strtotime('-1 hour'))])
            ->all();

        foreach ($tasks as $task) {
            $task->status = Task::STATUS_ERROR;
            $task->message = 'Зависший процесс';
            $task->save();
        }
    }
}
