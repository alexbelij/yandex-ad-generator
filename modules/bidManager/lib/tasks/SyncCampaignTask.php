<?php

namespace app\modules\bidManager\lib\tasks;

use app\modules\bidManager\lib\sync\CampaignYandexSync;

/**
 * Class SyncCampaignTask
 * @package app\modules\bidManager\lib\tasks
 */
class SyncCampaignTask extends AbstractTask
{
    /**
     * @inheritDoc
     */
    public function execute($params = [])
    {
        $synchronizer = new CampaignYandexSync($this->getLogger());
        $synchronizer->sync($this->task->account);
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'Синхронизация кампаний';
    }
}
