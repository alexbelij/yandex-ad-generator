<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "ad_template_category".
 *
 * @property integer $ad_template_id
 * @property integer $category_id
 *
 * @property AdTemplate $adTemplate
 * @property ExternalCategory $category
 */
class AdTemplateCategory extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ad_template_category';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ad_template_id', 'category_id'], 'required'],
            [['ad_template_id', 'category_id'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'ad_template_id' => 'Ad Template ID',
            'category_id' => 'Category ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAdTemplate()
    {
        return $this->hasOne(AdTemplate::className(), ['id' => 'ad_template_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCategory()
    {
        return $this->hasOne(ExternalCategory::className(), ['id' => 'category_id']);
    }
}
