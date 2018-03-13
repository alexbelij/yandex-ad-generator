<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "campaign_template_brand".
 *
 * @property integer $campaign_template_id
 * @property integer $brand_id
 */
class CampaignTemplateBrand extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'campaign_template_brand';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['campaign_template_id', 'brand_id'], 'required'],
            [['campaign_template_id', 'brand_id'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'campaign_template_id' => 'Campaign Template ID',
            'brand_id' => 'Brand ID',
        ];
    }
}
