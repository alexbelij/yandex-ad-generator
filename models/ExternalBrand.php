<?php

namespace app\models;

use app\behaviors\FileLogBehavior;
use app\helpers\StringHelper;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "external_brand".
 *
 * @property integer $id
 * @property string $title
 * @property integer $shop_id
 * @property string $created_at
 * @property string $updated_at
 * @property integer $outer_id
 * @property bool $is_deleted
 * @property bool $is_manual
 * @property string $original_title
 *
 * @property Shop $shop
 */
class ExternalBrand extends ExternalEntity
{
    const DEFAULT_BRAND_TITLE = 'Unknown';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'external_brand';
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
            [['shop_id', 'outer_id'], 'integer'],
            [['title', 'original_title'], 'string', 'max' => 255],
            [['created_at', 'updated_at'], 'string'],
            [['is_deleted', 'is_manual'], 'boolean']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Заголовок',
            'shop_id' => 'Магазин',
            'created_at' => 'Дата создания',
            'updated_at' => 'Дата обновления',
            'is_deleted' => 'Не показывать',
            'is_manual' => 'Не обновлять при импорте',
            'original_title' => 'Оригинальное название'
        ];
    }

    /**
     * @param int $shopId
     * @return ExternalBrand
     */
    public static function getDefaultBrand($shopId)
    {
        /** @var ExternalBrand $brand */
        $brand = self::find()->andWhere(['shop_id' => $shopId, 'title' => self::DEFAULT_BRAND_TITLE])->one();
        if (!$brand) {
            $brand = new static(['shop_id' => $shopId, 'title' => self::DEFAULT_BRAND_TITLE]);
            $brand->save();
        }

        return $brand;
    }

    /**
     * @return bool
     */
    public function isUnknown()
    {
        return $this->title == self::DEFAULT_BRAND_TITLE;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShop()
    {
        return $this->hasOne(Shop::className(), ['id' => 'shop_id']);
    }

    /**
     * @return Variation
     */
    public function getVariation()
    {
        $entityId = $this->shop->isFileLoadStrategy() ? $this->primaryKey : $this->outer_id;

        return Variation::find()
            ->andWhere([
                'shop_id' => $this->shop_id,
                'entity_type' => Variation::TYPE_BRAND,
                'entity_id' => $entityId
            ])->one();
    }

    /**
     * @return string[]
     */
    public function getVariations()
    {
        $variation = $this->getVariation();

        if ($variation) {
            return $variation->getVariationList(false);
        }

        return [];
    }
}
