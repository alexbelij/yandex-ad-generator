<?php

namespace app\modules\bidManager\models\query;

/**
 * This is the ActiveQuery class for [[\app\modules\bidManager\models\YandexKeyword]].
 *
 * @see \app\modules\bidManager\models\YandexKeyword
 */
class YandexKeywordQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * @inheritdoc
     * @return \app\modules\bidManager\models\YandexKeyword[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return \app\modules\bidManager\models\YandexKeyword|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
