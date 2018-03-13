<?php

namespace app\models;

use app\behaviors\FileLogBehavior;
use app\helpers\ArrayHelper;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "external_product".
 *
 * @property integer $id
 * @property string $outer_id
 * @property integer $shop_id
 * @property string $title
 * @property integer $brand_id
 * @property integer $category_id
 * @property bool $is_available
 * @property string $picture
 * @property string $url
 * @property string $currency_id
 * @property double $old_price
 * @property double $price
 * @property string $created_at
 * @property string $updated_at
 * @property int $file_import_id
 * @property string $model
 * @property string $original_title
 * @property string $type_prefix
 * @property bool $is_manual
 * @property bool $is_available_on_link
 * @property bool $is_url_available
 * @property string $available_check_at
 * @property boolean $is_generate_ad
 *
 * @property ExternalCategory $category
 * @property ExternalBrand $brand
 * @property Shop $shop
 */
class ExternalProduct extends ExternalEntity
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'external_product';
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
                    self::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
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
            [['shop_id', 'brand_id', 'category_id', 'file_import_id'], 'integer'],
            [['model', 'original_title', 'outer_id', 'type_prefix', 'available_check_at'], 'string'],
            [['old_price', 'price'], 'number'],
            [['is_available', 'is_manual', 'is_url_available', 'is_generate_ad'], 'boolean'],
            [['title'], 'string', 'max' => 255],
            [['picture', 'url'], 'string', 'max' => 1024],
            [['currency_id'], 'string', 'max' => 11],
            [['outer_id', 'shop_id'], 'unique', 'targetAttribute' => ['outer_id', 'shop_id'], 'message' => 'The combination of Outer ID and Shop ID has already been taken.'],
            [['created_at', 'updated_at'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'outer_id' => 'Внешний ид',
            'shop_id' => 'Магазин',
            'title' => 'Модель',
            'brand_id' => 'Бренд',
            'category_id' => 'Категория',
            'is_available' => 'Наличие товара',
            'picture' => 'Ссылка на изображение товара',
            'url' => 'Ссылка на товар',
            'currency_id' => 'Валюта',
            'old_price' => 'Old Price',
            'price' => 'Цена',
            'is_manual' => 'Не обновлять при импорте',
            'original_title' => 'Оригинальное название',
            'is_generate_ad' => 'Генерировать объявления для товара',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCategory()
    {
        return $this->hasOne(ExternalCategory::className(), ['id' => 'category_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBrand()
    {
        return $this->hasOne(ExternalBrand::className(), ['id' => 'brand_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShop()
    {
        return $this->hasOne(Shop::className(), ['id' => 'shop_id']);
    }

    /**
     * @return array
     */
    public function getCategoriesList()
    {
        $items = ExternalCategory::find()
            ->select([$this->getField(), 'title'])
            ->andWhere(['shop_id' => $this->shop_id])
            ->asArray()
            ->all();

        return ArrayHelper::map($items, $this->getField(), 'title');
    }

    /**
     * @return array
     */
    public function getBrandsList()
    {
        $items = ExternalBrand::find()
            ->select([$this->getField(), 'title'])
            ->andWhere(['shop_id' => $this->shop_id])
            ->asArray()
            ->all();

        return ArrayHelper::map($items, $this->getField(), 'title');
    }

    /**
     * @return string
     */
    protected function getField()
    {
        return ($this->shop->external_strategy == Shop::EXTERNAL_STRATEGY_API) ?
            'outer_id' : 'id';
    }

    /**
     * @return string
     */
    public function getShortUrl()
    {
        $url = $this->url;
        $url = str_replace(['http://', 'https://'], '', $url);
        $pos = strpos($url, '/');

        return substr($url, $pos + 1);
    }
}
