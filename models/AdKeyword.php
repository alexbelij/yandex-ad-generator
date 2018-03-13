<?php

namespace app\models;

use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "{{%ad_keyword}}".
 *
 * @property integer $id
 * @property integer $ad_id
 * @property string $keyword
 * @property bool $is_generated
 * @property integer $yandex_id
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Ad $ad
 */
class AdKeyword extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ad_keyword}}';
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
                    self::EVENT_BEFORE_INSERT => ['created_at'],
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
            [['ad_id', 'keyword'], 'required'],
            [['ad_id', 'yandex_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['keyword'], 'string'],
            [['is_generated'], 'boolean'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Id',
            'ad_id' => 'Ad ID',
            'keyword' => 'Keyword',
            'is_generated' => 'Объявление сгенерировано',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAd()
    {
        return $this->hasOne(Ad::className(), ['id' => 'ad_id']);
    }

    /**
     * @inheritDoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        if (array_key_exists('keyword', $changedAttributes)) {
            $this->ad->updated_at = date(self::DATETIME_FORMAT);
            $this->ad->save();
        }
    }

    /**
     * @inheritDoc
     */
    public function afterDelete()
    {
        parent::afterDelete();
        $this->ad->updated_at = date(self::DATETIME_FORMAT);
        $this->ad->save();
    }
}
