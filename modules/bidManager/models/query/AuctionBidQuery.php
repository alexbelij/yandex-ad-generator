<?php

namespace app\modules\bidManager\models\query;

/**
 * This is the ActiveQuery class for [[\app\modules\bidManager\models\AuctionBid]].
 *
 * @see \app\modules\bidManager\models\AuctionBid
 */
class AuctionBidQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * @inheritdoc
     * @return \app\modules\bidManager\models\AuctionBid[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return \app\modules\bidManager\models\AuctionBid|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
