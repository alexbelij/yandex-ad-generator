<?php

namespace app\lib;

use app\models\TaskQueue;
use app\models\YandexUpdateLog;

/**
 * Class YandexOperationLogger
 * @package app\lib
 * @author Denis Dyadyun <sysadm85@gmail.com>
 */
class YandexOperationLogger
{
    /**
     * @var TaskQueue
     */
    protected $task;

    /**
     * @var PointsCalculator
     */
    protected $pointsCalculator;

    /**
     * YandexOperationLogger constructor.
     * @param TaskQueue $task
     */
    public function __construct(TaskQueue $task)
    {
        $this->task = $task;
    }

    /**
     * @return PointsCalculator
     */
    public function getPointsCalculator()
    {
        if (is_null($this->pointsCalculator)) {
            $this->pointsCalculator = PointsCalculator::getInstance();
        }

        return $this->pointsCalculator;
    }

    /**
     * @param PointsCalculator $pointsCalculator
     */
    public function setPointsCalculator($pointsCalculator)
    {
        $this->pointsCalculator = $pointsCalculator;
    }

    /**
     * Логирование информации об операции
     *
     * @param LoggedInterface $model
     * @param string $operation
     * @param string $status
     * @param string $message
     */
    public function log(
        LoggedInterface $model, $operation, $status = YandexUpdateLog::STATUS_SUCCESS, $message = '', $points = 0
    ) {

        if (!$points) {
            $points = $this->getPointsCalculator()->getLastPointsAndClean();
        }

        $log = new YandexUpdateLog([
            'task_id' => $this->task->id,
            'shop_id' => $this->task->shop_id,
            'entity_type' => $model->getEntityType(),
            'entity_id' => $model->getEntityId(),
            'operation' => $operation,
            'status' => $status,
            'message' => $message,
            'points' => $points
        ]);

        $log->save();
    }
}
