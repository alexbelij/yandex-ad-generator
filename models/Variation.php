<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "variations".
 *
 * @property integer $id
 * @property integer $shop_id
 * @property string $entity_type
 * @property integer $entity_id
 * @property string $shuffle_name
 *
 * @property Shop $shop
 * @property VariationItem[] $variationItems
 */
class Variation extends ActiveRecord
{
    const TYPE_CATEGORY = 'category';
    const TYPE_BRAND = 'brand';
    
    /**
     * @var string
     */
    public $title;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'variation';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['shop_id', 'entity_id'], 'integer'],
            [['variation'], 'string'],
            [['entity_type', 'shuffle_name'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'shop_id' => 'Магазин',
            'entity_type' => 'Тип сущности',
            'entity_id' => 'Ид сущности',
            'variation' => 'Вариации',
            'shuffle_name' => 'Название для мало показов',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShop()
    {
        return $this->hasOne(Shop::className(), ['id' => 'shop_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVariationItems()
    {
        return $this->hasMany(VariationItem::className(), ['variation_id' => 'id']);
    }

    /**
     * @param bool $isUseInGeneration только те вариации, которые используются при генерации заголовков
     * @return array
     */
    public function getVariationList($isUseInGeneration = true)
    {
        $query = $this->getVariationItems();
        if ($isUseInGeneration) {
            $query->andWhere(['is_use_in_generation' => true]);
        }

        return $query->select('value')->column();
    }
}
