<?php

namespace app\models;

use app\behaviors\JsonFieldsBehavior;
use app\helpers\AccountHelper;
use app\models\search\ProductsSearch;
use Yii;

/**
 * This is the model class for table "account".
 *
 * @property integer $id
 * @property string $title
 * @property string $token
 * @property string $account_data
 * @property string $account_type
 * @property string $units
 *
 * @property Shop[] $shops
 */
class Account extends BaseModel
{
    const ACCOUNT_TYPE_YANDEX = 'yandex';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%account}}';
    }

    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => JsonFieldsBehavior::className(),
                'fields' => ['account_data']
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['account_data'], 'safe'],
            [['account_type'], 'string'],
            [['title', 'token'], 'string', 'max' => 255],
            [['units'], 'string']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Название аккаунта',
            'token' => 'Токен',
            'account_data' => 'Данные аккаунта',
            'account_type' => 'Тип аккаунта',
            'units' => 'Баллы (осталось/лимит)'
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShops()
    {
        return $this->hasMany(Shop::className(), ['id' => 'account_id']);
    }

    /**
     * @return int
     */
    public function getAvailableUnits()
    {
        return (int) substr($this->units, 0, strpos($this->units, '/'));
    }

    /**
     * @return bool
     */
    public function hasAvailableUnits()
    {
        return $this->getAvailableUnits() - 100 > 0;
    }
}
