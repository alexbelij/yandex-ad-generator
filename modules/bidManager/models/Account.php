<?php

namespace app\modules\bidManager\models;

use Yii;

/**
 * This is the model class for table "bid_account".
 *
 * @property integer $id
 * @property string $title
 * @property string $token
 * @property string $settings
 * @property string $type
 * @property string $last_updated_at
 * @property int $max_click_price
 * @property int $strategy_1
 * @property int $strategy_2
 * @property string $units
 *
 * @property YandexAdGroup[] $bidYandexAdGroups
 */
class Account extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'bid_account';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title'], 'required'],
            [['settings', 'units'], 'string'],
            [['last_updated_at'], 'safe'],
            [['max_click_price', 'strategy_1', 'strategy_2'], 'integer'],
            [['title', 'token', 'type'], 'string', 'max' => 255],
            [['strategy_1', 'strategy_2'], 'filter', 'filter' => function ($val) {
                return $val ?: null;
            }]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Заголовок',
            'token' => 'Секретный токен',
            'settings' => 'Настройки',
            'type' => 'Тип аккаунта',
            'max_click_price' => 'Максимальная цена клика',
            'last_updated_at' => 'Дата последнего обновления',
            'strategy_1' => 'Основная стратегия',
            'strategy_2' => 'Доп стратегия',
            'units' => 'Баллы',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBidYandexAdGroups()
    {
        return $this->hasMany(YandexAdGroup::className(), ['account_id' => 'id']);
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
