<?php

namespace app\modules\bidManager\lib\bidStrategies;

use app\modules\bidManager\models\BidUpdateModel;
use app\modules\bidManager\models\Strategy;

/**
 * Class BaseStrategy
 * @package app\modules\bidManager\lib\bidStrategies
 */
abstract class BaseStrategy implements BidStrategyInterface
{
    /**
     * @var string
     */
    protected $patternRegexp;

    /**
     * @var string
     */
    protected $fieldPrefix;

    /**
     * @inheritDoc
     */
    public function isSpecifyBy(BidUpdateModel $model, Strategy $strategy)
    {
        $val = $this->getBid($model, $strategy);

        if (!$val) {
            return false;
        }

        if ($model->maxClickPrice >= $val || !$model->maxClickPrice) {
            return true;
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function getBid(BidUpdateModel $model, Strategy $strategy)
    {
        $matches = [];
        if (!preg_match($this->patternRegexp, $strategy->strategy, $matches)) {
            return null;
        }

        $posNum = (int)$matches[1];
        $percent = (int)$matches[2];

        $field = "{$this->fieldPrefix}{$posNum}_bid";
        $bidPrice = $model->bidAuction->{$field};

        if (!$bidPrice) {
            return null;
        }

        return $bidPrice + $bidPrice * $percent / 100;
    }
}
