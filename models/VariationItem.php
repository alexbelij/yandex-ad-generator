<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "variation_item".
 *
 * @property integer $id
 * @property integer $variation_id
 * @property string $value
 * @property integer $is_use_in_generation
 *
 * @property Variation $variation0
 */
class VariationItem extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'variation_item';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['variation_id', 'is_use_in_generation'], 'integer'],
            [['value'], 'string', 'max' => 1024]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'variation_id' => 'Вариация',
            'value' => 'Текст вариации',
            'is_use_in_generation' => 'Использовать вариацию при генерации заголовков',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVariation()
    {
        return $this->hasOne(Variation::className(), ['id' => 'variation_id']);
    }
}
