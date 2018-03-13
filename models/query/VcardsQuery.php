<?php

namespace app\models\query;

/**
 * This is the ActiveQuery class for [[\app\models\Vcard]].
 *
 * @see \app\models\Vcard
 */
class VcardsQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }*/

    /**
     * @inheritdoc
     * @return \app\models\Vcard[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return \app\models\Vcard|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}