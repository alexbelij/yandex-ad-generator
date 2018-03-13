<?php

namespace app\modules\feed\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "feed_item".
 *
 * @property integer $id
 * @property integer $feed_queue_id
 * @property integer $brand_id
 * @property integer $category_id
 * @property integer $price
 * @property integer $is_active
 * @property string $item_text
 * @property string $outer_id
 * @property integer $feed_id
 * @property string $created_at
 * @property string $updated_at
 * @property string $name
 *
 * @property FeedBrand $brand
 * @property FeedCategory $category
 * @property Feed $feed0
 */
class FeedItem extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'feed_item';
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
            [['feed_queue_id', 'brand_id', 'category_id', 'price', 'is_active', 'feed_id'], 'integer'],
            [['item_text', 'name'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['outer_id'], 'string', 'max' => 255],
            [['brand_id'], 'exist', 'skipOnError' => true, 'targetClass' => FeedBrand::className(), 'targetAttribute' => ['brand_id' => 'id']],
            [['feed_id', 'category_id'], 'exist', 'skipOnError' => true, 'targetClass' => FeedCategory::className(), 'targetAttribute' => ['feed_id' => 'feed_id', 'category_id' => 'id']],
            [['feed_id'], 'exist', 'skipOnError' => true, 'targetClass' => Feed::className(), 'targetAttribute' => ['feed_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'feed_queue_id' => 'Загруженный фид',
            'brand_id' => 'Бренд',
            'category_id' => 'Категория',
            'price' => 'Цена',
            'is_active' => 'Активность',
            'item_text' => 'Содержимое элемента',
            'outer_id' => 'Оригинальный id',
            'feed_id' => 'Фид',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBrand()
    {
        return $this->hasOne(FeedBrand::className(), ['id' => 'brand_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCategory()
    {
        return $this->hasOne(FeedCategory::className(), ['feed_id' => 'feed_id', 'id' => 'category_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFeed0()
    {
        return $this->hasOne(Feed::className(), ['id' => 'feed_id']);
    }
}
