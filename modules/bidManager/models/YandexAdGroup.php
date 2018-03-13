<?php

namespace app\modules\bidManager\models;

use app\models\BaseModel;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "bid_yandex_ad_group".
 *
 * @property string $id
 * @property integer $campaign_id
 * @property integer $account_id
 * @property string $created_at
 * @property string $updated_at
 * @property string $name
 * @property string $status
 * @property string $type
 *
 * @property Account $account
 */
class YandexAdGroup extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'bid_yandex_ad_group';
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
            [['id', 'campaign_id', 'account_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['name', 'status', 'type'], 'string', 'max' => 255],
            [['account_id'], 'exist', 'skipOnError' => true, 'targetClass' => Account::className(), 'targetAttribute' => ['account_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'campaign_id' => 'Campaign ID',
            'account_id' => 'Account ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'name' => 'Name',
            'status' => 'Status',
            'type' => 'Type',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAccount()
    {
        return $this->hasOne(Account::className(), ['id' => 'account_id']);
    }
}
