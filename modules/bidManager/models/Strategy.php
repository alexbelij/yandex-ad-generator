<?php

namespace app\modules\bidManager\models;

use Yii;

/**
 * This is the model class for table "bid_strategy".
 *
 * @property integer $id
 * @property string $title
 * @property string $strategy
 * @property string $delegee
 *
 * @property YandexAdGroup[] $bidYandexAdGroups
 * @property YandexAdGroup[] $bidYandexAdGroups0
 * @property YandexCampaign[] $bidYandexCampaigns
 * @property YandexCampaign[] $bidYandexCampaigns0
 */
class Strategy extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'bid_strategy';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title', 'delegee', 'strategy'], 'string', 'max' => 255],
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
            'strategy' => 'Стратегия',
            'delegee' => 'Класс стретегии',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBidYandexAdGroups()
    {
        return $this->hasMany(YandexAdGroup::className(), ['strategy_1' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBidYandexAdGroups0()
    {
        return $this->hasMany(YandexAdGroup::className(), ['strategy_2' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBidYandexCampaigns()
    {
        return $this->hasMany(YandexCampaign::className(), ['strategy_1' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBidYandexCampaigns0()
    {
        return $this->hasMany(YandexCampaign::className(), ['strategy_2' => 'id']);
    }
}
