<?php

namespace app\modules\bidManager\models;

/**
 * Class BidUpdateModel
 * @package app\modules\bidManager\models
 *
 * @param Strategy $strategy1
 * @param Strategy $strategy2
 */
class BidUpdateModel extends YandexBid
{
    /**
     * @var int
     */
    public $strategy_1;

    /**
     * @var int
     */
    public $strategy_2;

    /**
     * @var float
     */
    public $maxClickPrice;

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStrategy1()
    {
        return $this->hasOne(Strategy::className(), ['id' => 'strategy_1'])
            ->from(['bs1' => Strategy::tableName()]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStrategy2()
    {
        return $this->hasOne(Strategy::className(), ['id' => 'strategy_2'])
            ->from(['bs2' => Strategy::tableName()]);
    }
}
