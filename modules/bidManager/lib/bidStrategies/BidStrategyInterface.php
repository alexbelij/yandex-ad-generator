<?php

namespace app\modules\bidManager\lib\bidStrategies;

use app\modules\bidManager\models\BidUpdateModel;
use app\modules\bidManager\models\Strategy;

/**
 * Interface BidStrategyInterface
 * @package app\modules\bidManager\lib\bidStrategies
 */
interface BidStrategyInterface
{
    /**
     * @param BidUpdateModel $model
     * @param Strategy $strategy
     * @return mixed
     */
    public function isSpecifyBy(BidUpdateModel $model, Strategy $strategy);


    /**
     * @param BidUpdateModel $model
     * @param Strategy $strategy
     * @return mixed
     */
    public function getBid(BidUpdateModel $model, Strategy $strategy);
}
