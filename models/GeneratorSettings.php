<?php

namespace app\models;

use app\behaviors\JsonFieldsBehavior;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "generator_settings".
 *
 * @property integer $id
 * @property integer $shop_id
 * @property double $price_from
 * @property double $price_to
 * @property string $brands
 * @property string $filter
 *
 * @property array $categoryIds
 * @property Shop $shop
 */
class GeneratorSettings extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'generator_settings';
    }

    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => JsonFieldsBehavior::className(),
                'fields' => ['filter']
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['shop_id'], 'integer'],
            [['price_from', 'price_to'], 'number'],
            [['brands'], 'string'],
            [['categoryIds', 'filter'], 'safe']
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
            'price_from' => 'Price From',
            'price_to' => 'Price To',
            'brands' => 'Brands',
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
     * @return mixed
     */
    public function getCategoryIds()
    {
        return ArrayHelper::getValue($this->filter, 'categoryIds', []);
    }

    /**
     * @param array $ids
     * @return $this
     */
    public function setCategoryIds(array $ids)
    {
        $this->filter = array_merge((array)$this->filter, ['categoryIds' => array_filter((array)$ids)]);
        return $this;
    }

    /**
     * @param int $shopId
     * @return GeneratorSettings
     */
    public static function forShop($shopId)
    {
        return self::find()->andWhere(['shop_id' => $shopId])->one();
    }

}
