<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "ad_template_brand".
 *
 * @property integer $ad_template_id
 * @property integer $brand_id
 *
 * @property AdTemplate $adTemplate
 */
class AdTemplateBrand extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ad_template_brand';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ad_template_id', 'brand_id'], 'required'],
            [['ad_template_id', 'brand_id'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'ad_template_id' => 'Ad Template ID',
            'brand_id' => 'Brand ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAdTemplate()
    {
        return $this->hasOne(AdTemplate::className(), ['id' => 'ad_template_id']);
    }
}
