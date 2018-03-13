<?php

namespace app\lib\api\yandex\direct\resources;

use app\helpers\ArrayHelper;
use app\lib\api\yandex\direct\query\BidQuery;
use app\lib\api\yandex\direct\query\ChangeResult;
use app\lib\PointsCalculator;

/**
 * Class BidResource
 * @package app\lib\api\yandex\direct\resources
 */
class BidResource extends AbstractResource
{
    public $resourceName = 'bids';

    public $queryClass = BidQuery::class;

    /**
     * @param array $params
     * @return ChangeResult
     */
    public function set($params)
    {
        $resourceName = ucfirst($this->getBaseStructure());
        if (ArrayHelper::isAssociative($params)) {
            $params = [$params];
        }

        $data = [$resourceName => $params];

        $result = $this->connection->query($this->resourceName, $data, 'set');
        $this->getPointsCalculator()->inc($this->getType(), PointsCalculator::OP_SET, count($params));

        return new ChangeResult($result['result']['SetResults']);
    }
}
