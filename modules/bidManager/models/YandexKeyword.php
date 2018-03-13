<?php

namespace app\modules\bidManager\models;

use app\models\BaseModel;
use app\modules\bidManager\models\query\YandexKeywordQuery;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "bid_yandex_keyword".
 *
 * @property string $id
 * @property string $keyword
 * @property integer $group_id
 * @property integer $campaign_id
 * @property string $bid
 * @property string $context_bid
 * @property string $state
 * @property string $status
 * @property integer $stat_search_clicks
 * @property integer $stat_search_impressions
 * @property integer $stat_network_clicks
 * @property integer $stat_network_impressions
 * @property integer $account_id
 * @property string $created_at
 * @property string $updated_at
 * @property float $max_click_price
 * @property int $strategy_1
 * @property int $strategy_2
 *
 * @property Strategy $strategy1
 * @property Strategy $strategy2
 */
class YandexKeyword extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'bid_yandex_keyword';
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
            [['id', 'group_id', 'campaign_id', 'stat_search_clicks', 'stat_search_impressions', 'stat_network_clicks', 'stat_network_impressions', 'account_id', 'strategy_1', 'strategy_2', 'max_click_price'], 'integer'],
            [['bid', 'context_bid'], 'number'],
            [['created_at', 'updated_at'], 'safe'],
            [['keyword', 'state', 'status'], 'string', 'max' => 255],
            [['strategy_1', 'strategy_2'], 'filter', 'filter' => function ($val) {
                return $val ?: null;
            }],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Id',
            'keyword' => 'Ключевое слово',
            'group_id' => 'Группа объявления',
            'campaign_id' => 'Кампания',
            'bid' => 'Ставка на поиске',
            'context_bid' => 'Ставка в сетях',
            'state' => 'Состояние ключевой фразы',
            'status' => 'Статус ключевой фразы',
            'stat_search_clicks' => 'Количество кликов по всем объявлениям группы, показанным по данной фразе',
            'stat_search_impressions' => 'Количество показов всех объявлений группы по данной фразе',
            'stat_network_clicks' => 'Количество кликов по всем объявлениям группы, показанным по данной фразе',
            'stat_network_impressions' => 'Количество показов всех объявлений группы по данной фразе',
            'max_click_price' => 'Максимальная цена клика',
            'strategy_1' => 'Осн стратегия',
            'strategy_2' => 'Доп стратегия'
        ];
    }

    /**
     * @inheritdoc
     * @return YandexKeywordQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new YandexKeywordQuery(get_called_class());
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStrategy1()
    {
        return $this->hasOne(Strategy::className(), ['id' => 'strategy_1']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStrategy2()
    {
        return $this->hasOne(Strategy::className(), ['id' => 'strategy_2']);
    }
}
