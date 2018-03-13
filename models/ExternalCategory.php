<?php

namespace app\models;

use app\behaviors\FileLogBehavior;
use app\helpers\StringHelper;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "external_category".
 *
 * @property integer $id
 * @property integer $outer_id
 * @property integer $shop_id
 * @property string $title
 * @property integer $parent_id
 * @property string $created_at
 * @property string $updated_at
 * @property boolean $is_manual
 * @property string $original_title
 *
 * @property ExternalProduct[] $externalProducts
 * @property ExternalCategory $parent
 */
class ExternalCategory extends ExternalEntity
{
    const UNKNOWN_TITLE = 'Unknown';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'external_category';
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
            [['outer_id', 'shop_id', 'parent_id'], 'integer'],
            [['title', 'original_title'], 'string', 'max' => 255],
            [['created_at', 'updated_at'], 'string'],
            ['variations', 'string', 'max' => 1024],
            [['is_manual'], 'boolean']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ИД',
            'outer_id' => 'Внешний ИД',
            'shop_id' => 'Магазин',
            'title' => 'Заголовок',
            'parent_id' => 'Родитель',
            'variations' => 'Вариации',
            'is_manual' => 'Не обновлять при импорте',
            'original_title' => 'Оригинальное название',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getExternalProducts()
    {
        return $this->hasMany(ExternalProduct::className(), ['category_id' => 'id']);
    }

    /**
     * @param int $shopId
     * @return ExternalCategory|static
     */
    public static function getDefaultCategory($shopId)
    {
        /** @var ExternalCategory $category */
        $category = self::find()->andWhere(['shop_id' => $shopId, 'title' => self::UNKNOWN_TITLE])->one();
        if (!$category) {
            $category = new static(['shop_id' => $shopId, 'title' => self::UNKNOWN_TITLE]);
            $category->save();
        }

        return $category;
    }

    /**
     * @return bool
     */
    public function isUnknown()
    {
        return $this->title == self::UNKNOWN_TITLE;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParent()
    {
        return $this->hasOne(ExternalCategory::className(), ['outer_id' => 'parent_id'])->andWhere(['shop_id' => $this->shop_id]);
    }

    /**
     * @return string[]
     */
    public function getVariations()
    {
        /** @var Variation $variation */
        $variation = Variation::find()
            ->andWhere([
                'shop_id' => $this->shop_id,
                'entity_type' => Variation::TYPE_CATEGORY,
                'entity_id' => $this->primaryKey
            ])->one();

        if ($variation) {
            return $variation->getVariationList(false);
        }

        return [];
    }
}
