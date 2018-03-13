<?php

namespace app\modules\feed\models;

use Yii;

/**
 * This is the model class for table "feed_brand".
 *
 * @property integer $id
 * @property integer $feed_queue_id
 * @property string $title
 * @property integer $feed_id
 *
 * @property Feed $feed
 * @property FeedItem[] $feedItems
 */
class FeedBrand extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'feed_brand';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['feed_queue_id', 'feed_id'], 'integer'],
            [['title'], 'string', 'max' => 255],
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
            'title' => 'Название бренда',
            'feed_id' => 'Фид',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFeed()
    {
        return $this->hasOne(Feed::className(), ['id' => 'feed_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFeedItems()
    {
        return $this->hasMany(FeedItem::className(), ['brand_id' => 'id']);
    }
}
