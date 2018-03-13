<?php

namespace app\modules\bidManager\models\query;

/**
 * This is the ActiveQuery class for [[\app\modules\bidManager\models\BidAdSearchPrice]].
 *
 * @see \app\modules\bidManager\models\BidAdSearchPrice
 */
class BidAdSearchPriceQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * @inheritdoc
     * @return \app\modules\bidManager\models\BidAdSearchPrice[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return \app\modules\bidManager\models\BidAdSearchPrice|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
