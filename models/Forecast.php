<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "forecast".
 *
 * @property integer $id
 * @property integer $shop_id
 * @property integer $brand_id
 * @property integer $points
 *
 * @property Shop $shop
 */
class Forecast extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'forecast';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['shop_id', 'brand_id', 'points'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'shop_id' => 'Shop ID',
            'brand_id' => 'Brand ID',
            'points' => 'Points',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShop()
    {
        return $this->hasOne(Shop::className(), ['id' => 'shop_id']);
    }
}
