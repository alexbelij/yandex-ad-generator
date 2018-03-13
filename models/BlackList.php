<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "black_list".
 *
 * @property integer $id
 * @property string $name
 * @property string $type
 * @property int $shop_id
 *
 * @property Shop $shop
 */
class BlackList extends BaseModel
{
    const TYPE_KEYWORD = 'keyword';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'black_list';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'string', 'max' => 1024],
            [['type'], 'string', 'max' => 255],
            [['shop_id'], 'integer'],
            [['name'], 'unique']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Фраза',
            'type' => 'Тип',
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
