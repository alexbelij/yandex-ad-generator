<?php

namespace app\models;

use app\lib\LoggedInterface;
use Yii;

/**
 * This is the model class for table "ad_yandex_campaign".
 *
 * @property integer $id
 * @property integer $ad_id
 * @property integer $yandex_campaign_id
 * @property integer $template_id
 * @property integer $yandex_ad_id
 * @property integer $yandex_adgroup_id
 * @property string $uploaded_at
 * @property bool $is_published
 * @property integer $account_id
 * @property string $status
 * @property string $state
 * @property string $yandex_group_name
 * @property integer $ad_yandex_group_id
 *
 * @property Ad $ad
 * @property AdTemplate $template
 * @property YandexCampaign $yandexCampaign
 * @property AdYandexGroup $adYandexGroup
 */
class AdYandexCampaign extends BaseModel implements LoggedInterface
{
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_MODERATION = 'moderation';
    const STATUS_DRAFT = 'draft';
    const STATUS_REJECTED = 'rejected';
    const STATUS_PREACCEPTED = 'preaccepted';

    const STATE_SUSPENDED = 'suspended';
    const STATE_OFF_BY_MONITORING = 'off_by_monitoring';
    const STATE_ON = 'on';
    const STATE_OFF = 'off';
    const STATE_ARCHIVED = 'archived';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ad_yandex_campaign';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ad_id', 'yandex_campaign_id', 'template_id', 'yandex_ad_id', 'yandex_adgroup_id', 'account_id', 'ad_yandex_group_id'], 'integer'],
            [['uploaded_at'], 'safe'],
            [['is_published'], 'boolean'],
            [['status', 'state', 'yandex_group_name'], 'string']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'ad_id' => 'Ad ID',
            'yandex_campaign_id' => 'Yandex Campaign ID',
            'template_id' => 'Template ID',
            'yandex_ad_id' => 'Yandex Ad ID',
            'yandex_adgroup_id' => 'Yandex Adgroup ID',
            'uploaded_at' => 'Uploaded At',
            'ad_yandex_group_id' => 'Ad Yandex Group ID',
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
     * @return \yii\db\ActiveQuery
     */
    public function getTemplate()
    {
        return $this->hasOne(AdTemplate::className(), ['id' => 'template_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getYandexCampaign()
    {
        return $this->hasOne(YandexCampaign::className(), ['id' => 'yandex_campaign_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAdYandexGroup()
    {
        return $this->hasOne(AdYandexGroup::className(), ['id' => 'ad_yandex_group_id']);
    }

    /**
     * @inheritDoc
     */
    public function getEntityType()
    {
        return 'YandexAd';
    }

    /**
     * @inheritDoc
     */
    public function getEntityId()
    {
        return $this->primaryKey;
    }

    /**
     * Обновить дату загрузки
     */
    public function updateUploadDate()
    {
        $this->uploaded_at = date(self::DATETIME_FORMAT);
        $this->save();
    }

    /**
     * @inheritDoc
     */
    public function beforeSave($insert)
    {
        if (!$this->status) {
            $this->status = self::STATUS_MODERATION; //статус по умолчанию
        }

        if (!$this->state) {
            $this->state = self::STATE_ON;
        }

        return parent::beforeSave($insert);
    }
}
