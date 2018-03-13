<?php

namespace app\modules\bidManager\models;

use app\modules\bidManager\models\query\BidAdSearchPriceQuery;
use Yii;

/**
 * This is the model class for table "bid_ad_search_price".
 *
 * @property integer $id
 * @property string $bid_id
 * @property string $footer_block_price
 * @property string $footer_first_price
 * @property string $premium_block_price
 * @property string $premium_first_price
 *
 * @property YandexBid $bid
 */
class AdSearchPrice extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'bid_ad_search_price';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['bid_id'], 'integer'],
            [['footer_block_price', 'footer_first_price', 'premium_block_price', 'premium_first_price'], 'number'],
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
            'bid_id' => 'Ставка',
            'footer_block_price' => 'минимальная ставка за 4-ю позицию в гарантии (вход в блок гарантированных показов',
            'footer_first_price' => 'минимальная ставка за 1-ю позицию в гарантии',
            'premium_block_price' => 'минимальная ставка за 3-ю позицию в спецразмещении (вход в спецразмещение)',
            'premium_first_price' => 'минимальная ставка за 1-ю позицию в спецразмещении',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBid()
    {
        return $this->hasOne(YandexBid::className(), ['id' => 'bid_id']);
    }

    /**
     * @inheritdoc
     * @return BidAdSearchPriceQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new BidAdSearchPriceQuery(get_called_class());
    }
}
