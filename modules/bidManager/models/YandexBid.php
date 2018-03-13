<?php

namespace app\modules\bidManager\models;

use app\models\BaseModel;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "bid_yandex_bid".
 *
 * @property string $id
 * @property integer $campaign_id
 * @property integer $group_id
 * @property integer $keyword_id
 * @property string $created_at
 * @property string $updated_at
 * @property string $bid_serving_status
 * @property integer $bid
 * @property integer $context_bid
 * @property integer $bid_min_search_price
 * @property integer $bid_current_search_price
 * @property string $competitors_bids
 *
 * @property AdSearchPrice $bidAdSearchPrice
 * @property AuctionBid $bidAuction
 * @property YandexKeyword $keyword
 * @property YandexAdGroup $adGroup
 * @property YandexCampaign $campaign
 * @property ContextCoverage[] $bidContextCoverages
 */
class YandexBid extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'bid_yandex_bid';
    }

    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'value' => new Expression('now()'),
                'attributes' => [
                    self::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
                    self::EVENT_BEFORE_UPDATE => ['updated_at']
                ]
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'campaign_id', 'group_id', 'keyword_id'], 'integer'],
            [['bid', 'context_bid', 'bid_min_search_price', 'bid_current_search_price'], 'number'],
            [['created_at', 'updated_at'], 'safe'],
            [['competitors_bids'], 'string'],
            [['bid_serving_status'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'campaign_id' => 'Кампания',
            'group_id' => 'Группа',
            'keyword_id' => 'Ключевое слово',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'bid_serving_status' => 'Bid Serving Status',
            'bid' => 'Ставка на поиске',
            'context_bid' => 'Context Bid',
            'bid_min_search_price' => 'Минимальная ставка на поиске',
            'bid_current_search_price' => 'Текущая цена клика на поиске',
            'competitors_bids' => 'Competitors Bids',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBidAdSearchPrice()
    {
        return $this->hasOne(AdSearchPrice::className(), ['bid_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBidAuction()
    {
        return $this->hasOne(AuctionBid::className(), ['bid_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBidContextCoverages()
    {
        return $this->hasMany(ContextCoverage::className(), ['bid_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getKeyword()
    {
        return $this->hasOne(YandexKeyword::className(), ['id' => 'keyword_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAdGroup()
    {
        return $this->hasOne(YandexAdGroup::className(), ['id' => 'group_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCampaign()
    {
        return $this->hasOne(YandexCampaign::className(), ['id' => 'campaign_id']);
    }
}
