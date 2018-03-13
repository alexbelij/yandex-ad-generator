<?php

namespace app\modules\bidManager\lib\tasks;

use app\modules\bidManager\lib\services\BidUpdaterService;

/**
 * Class SyncCampaignTask
 * @package app\modules\bidManager\lib\tasks
 */
class BidUpdateTask extends AbstractTask
{
    /**
     * @inheritDoc
     */
    public function execute($params = [])
    {
        $bidService = new BidUpdaterService($this->getLogger());
        $bidService->update($this->task->account);
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'Обновление ставок';
    }
}
