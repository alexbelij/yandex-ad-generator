<?php

namespace app\modules\bidManager\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "bid_yandex_campaign".
 *
 * @property string $id
 * @property integer $account_id
 * @property string $created_at
 * @property string $updated_at
 * @property string $title
 * @property string $start_date
 * @property string $end_date
 * @property string $status
 * @property string $state
 * @property string $status_payment
 * @property string $status_clarification
 * @property integer $stat_clicks
 * @property integer $stat_impressions
 * @property string $currency
 * @property string $funds_mode
 * @property float $funds_sum
 * @property float $funds_balance
 * @property float $funds_shared_refund
 * @property float $funds_shared_spend
 * @property string $client_info
 * @property float $daily_budget_amount
 * @property string $daily_budget_mode
 * @property integer $strategy_1
 * @property integer $strategy_2
 * @property integer $max_click_price
 *
 * @property Account $account
 * @property Strategy $strategy1
 * @property Strategy $strategy2
 */
class YandexCampaign extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'bid_yandex_campaign';
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
            [['id'], 'required'],
            [['id', 'account_id', 'stat_clicks', 'stat_impressions', 'strategy_1', 'strategy_2', 'max_click_price'], 'integer'],
            [['created_at', 'updated_at', 'start_date', 'end_date'], 'safe'],
            [['funds_sum', 'funds_balance', 'funds_shared_refund', 'funds_shared_spend', 'daily_budget_amount'], 'number'],
            [['title', 'status', 'state', 'status_payment', 'status_clarification', 'currency', 'funds_mode', 'client_info', 'daily_budget_mode'], 'string', 'max' => 255],
            [['account_id'], 'exist', 'skipOnError' => true, 'targetClass' => Account::className(), 'targetAttribute' => ['account_id' => 'id']],
            [['strategy_1', 'strategy_2'], 'filter', 'filter' => function ($val) {
                return $val ?: null;
            }],
            [['strategy_1'], 'exist', 'skipOnError' => true, 'targetClass' => Strategy::className(), 'targetAttribute' => ['strategy_1' => 'id']],
            [['strategy_2'], 'exist', 'skipOnError' => true, 'targetClass' => Strategy::className(), 'targetAttribute' => ['strategy_2' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'account_id' => 'Аккаунт',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'title' => 'Название',
            'start_date' => 'Дата начала',
            'end_date' => 'Дата окончания',
            'status' => 'Статус',
            'state' => 'Состояние',
            'status_payment' => 'Статус оплаты кампании',
            'status_clarification' => 'Текстовое пояснение к статусу',
            'stat_clicks' => 'Количество кликов за время существования кампании',
            'stat_impressions' => 'Количество показов за время существования кампании',
            'currency' => 'Валюта',
            'funds_mode' => 'Тип финансовых показателей кампании',
            'funds_sum' => 'Сумма средств, зачисленных на баланс кампании за время ее существования, в валюте рекламодателя, без НДС',
            'funds_balance' => 'Текущий баланс кампании в валюте рекламодателя, без НДС',
            'funds_shared_refund' => 'Сумма возврата средств за клики, признанные системой недобросовестными или ошибочными, без НДС',
            'funds_shared_spend' => 'Сумма средств, израсходованных по данной кампании за все время ее существования, без НДС',
            'client_info' => 'Название клиента',
            'daily_budget_amount' => 'Дневной бюджет кампании в валюте рекламодателя',
            'daily_budget_mode' => 'Тип дневного бюджета',
            'strategy_1' => 'Основная стратегия',
            'strategy_2' => 'Доп стратегия',
            'max_click_price' => 'Максимальная цена клика',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAccount()
    {
        return $this->hasOne(Account::className(), ['id' => 'account_id']);
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
