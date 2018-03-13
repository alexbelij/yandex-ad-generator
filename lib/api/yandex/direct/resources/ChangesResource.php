<?php

namespace app\lib\api\yandex\direct\resources;

use app\helpers\ArrayHelper;
use app\lib\api\yandex\direct\query\AbstractQuery;
use app\lib\api\yandex\direct\query\ChangesQuery;
use app\lib\api\yandex\direct\query\CheckResult;
use app\lib\PointsCalculator;

/**
 * Class ChangesResource
 * @package app\lib\api\yandex\direct\resources
 */
class ChangesResource extends AbstractResource
{
    /**
     * @var string
     */
    public $resourceName = 'changes';

    /**
     * @var string
     */
    public $queryClass = ChangesQuery::class;

    /**
     * @param $query
     * @param array $fieldNames
     * @return CheckResult
     */
    public function check($query, $fieldNames = [])
    {
        if (!$query instanceof AbstractQuery) {
            $query = new $this->queryClass($query);
        }

        if (!empty($fieldNames)) {
            $query->setFieldNames($fieldNames);
        }

        $result = $this->connection->query($this->resourceName, $query->getQuery(), 'check');
        $this->getPointsCalculator()->inc($this->getType(), PointsCalculator::OP_CHECK, 1);

        return new CheckResult(ArrayHelper::getValue($result, 'result', []));
    }
}
