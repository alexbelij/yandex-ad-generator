<?php

namespace app\modules\bidManager\models;

use Yii;

/**
 * This is the model class for table "bid_context_coverage".
 *
 * @property integer $id
 * @property string $bid_id
 * @property string $probability
 * @property integer $price
 *
 * @property YandexBid $bid
 */
class ContextCoverage extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'bid_context_coverage';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['bid_id'], 'integer'],
            [['probability', 'price'], 'number'],
            [['bid_id'], 'exist', 'skipOnError' => true, 'targetClass' => YandexBid::className(), 'targetAttribute' => ['bid_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'bid_id' => 'Bid ID',
            'probability' => 'Probability',
            'price' => 'Price',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBid()
    {
        return $this->hasOne(YandexBid::className(), ['id' => 'bid_id']);
    }
}
