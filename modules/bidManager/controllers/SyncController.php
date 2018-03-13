<?php

namespace app\modules\bidManager\controllers;

use app\modules\bidManager\lib\tasks\BidUpdateTask;
use app\modules\bidManager\lib\tasks\SyncCampaignTask;
use app\modules\bidManager\lib\tasks\SyncDeltaTask;
use app\modules\bidManager\lib\tasks\SyncFullTask;
use app\modules\bidManager\models\Task;
use Yii;

/**
 * Class SyncController
 * @package app\modules\bidManager\controllers
 */
class SyncController extends Controller
{
    /**
     * Запуск синхронизации кампаний
     *
     * @param $id
     */
    public function actionCampaign($id)
    {
        Task::createNewTask($id, SyncCampaignTask::class);
    }

    /**
     * Полная синхронизация
     *
     * @param $id
     */
    public function actionFull($id)
    {
        Task::createNewTask($id, SyncFullTask::class);
    }

    /**
     * Дельта синхронизация
     *
     * @param $id
     */
    public function actionDelta($id)
    {
        Task::createNewTask($id, SyncDeltaTask::class);
    }

    /**
     * Дельта синхронизация
     *
     * @param $id
     */
    public function actionBidUpdate($id)
    {
        Task::createNewTask($id, BidUpdateTask::class);
    }
}
