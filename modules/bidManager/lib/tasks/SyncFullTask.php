<?php

namespace app\modules\bidManager\lib\tasks;

use app\modules\bidManager\lib\sync\FullYandexSync;

/**
 * Class SyncCampaignTask
 * @package app\modules\bidManager\lib\tasks
 */
class SyncFullTask extends AbstractTask
{
    /**
     * @inheritDoc
     */
    public function execute($params = [])
    {
        $synchronizer = new FullYandexSync($this->getLogger());
        $synchronizer->sync($this->task->account);
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'Полная синхронизация';
    }
}
