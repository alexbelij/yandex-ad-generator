<?php

namespace app\modules\bidManager\models;

use app\modules\bidManager\models\query\AuctionBidQuery;
use Yii;

/**
 * This is the model class for table "bid_auction_bid".
 *
 * @property integer $id
 * @property string $bid_id
 * @property string $spec1_bid
 * @property string $spec1_price
 * @property string $spec2_bid
 * @property string $spec2_price
 * @property string $spec3_bid
 * @property string $spec3_price
 * @property string $gar1_bid
 * @property string $gar1_price
 * @property string $gar2_bid
 * @property string $gar2_price
 * @property string $gar3_bid
 * @property string $gar3_price
 * @property string $gar4_bid
 * @property string $gar4_price
 *
 * @property YandexBid $bid
 */
class AuctionBid extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'bid_auction_bid';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['bid_id'], 'integer'],
            [['spec1_bid', 'spec1_price', 'spec2_bid', 'spec2_price', 'spec3_bid', 'spec3_price', 'gar1_bid', 'gar1_price', 'gar2_bid', 'gar2_price', 'gar3_bid', 'gar3_price', 'gar4_bid', 'gar4_price'], 'number'],
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
            'spec1_bid' => 'Минимальная ставка спецразмещение 1 место',
            'spec1_price' => 'Списываемая цена за спецразмещение 1 место',
            'spec2_bid' => 'Минимальная ставка спецразмещение 2 место',
            'spec2_price' => 'Списываемая цена за спецразмещение 2 место',
            'spec3_bid' => 'Минимальная ставка спецразмещение 3 место',
            'spec3_price' => 'Списываемая цена за спецразмещение 3 место',
            'gar1_bid' => 'Минимальная ставка гарантированные показы 1 место',
            'gar1_price' => 'Списываемая цена за гарантированные показы 1 место',
            'gar2_bid' => 'Минимальная ставка гарантированные показы 2 место',
            'gar2_price' => 'Списываемая цена за гарантированные показы 2 место',
            'gar3_bid' => 'Минимальная ставка гарантированные показы 3 место',
            'gar3_price' => 'Списываемая цена за гарантированные показы 3 место',
            'gar4_bid' => 'Минимальная ставка гарантированные показы 4 место',
            'gar4_price' => 'Списываемая цена за гарантированные показы 4 место',
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
     * @return AuctionBidQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new AuctionBidQuery(get_called_class());
    }
}
