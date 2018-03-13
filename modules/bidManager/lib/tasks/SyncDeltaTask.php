<?php

namespace app\modules\bidManager\lib\tasks;

use app\modules\bidManager\lib\sync\DeltaYandexSync;
use app\modules\bidManager\models\Task;

/**
 * Class SyncCampaignTask
 * @package app\modules\bidManager\lib\tasks
 */
class SyncDeltaTask extends AbstractTask
{
    /**
     * @inheritDoc
     */
    public function execute($params = [])
    {
        $synchronizer = new DeltaYandexSync($this->getLogger());
        $synchronizer->sync($this->task->account);

        if (!Task::hasActiveTasks($this->task->account_id, BidUpdateTask::class)) {
            Task::createNewTask($this->task->account_id, BidUpdateTask::class);
        }
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'Синхронизация изменений (дельта)';
    }
}
