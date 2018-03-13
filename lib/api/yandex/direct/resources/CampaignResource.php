<?php

namespace app\lib\api\yandex\direct\resources;

use app\lib\api\yandex\direct\query\ChangeResult;
use app\lib\PointsCalculator;

/**
 * Class CampaignResource
 * @package app\lib\api\yandex\direct\resources
 */
class CampaignResource extends AbstractResource
{
    public $resourceName = 'campaigns';

    public $queryClass = 'app\lib\api\yandex\direct\query\CampaignQuery';

    /**
     * Остановка показа объявлений
     *
     * @param int|int[] $ids
     * @return ChangeResult
     */
    public function suspend($ids)
    {
        $ids = (array) $ids;

        $result = $this->query(['SelectionCriteria' => ['Ids' => array_values($ids)]], 'suspend');
        $this->getPointsCalculator()->inc($this->getType(), PointsCalculator::OP_SUSPEND, count($ids));

        return new ChangeResult($result['result']['SuspendResults']);
    }
}
