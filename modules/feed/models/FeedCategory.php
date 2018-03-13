<?php

namespace app\modules\feed\models;

use Yii;

/**
 * This is the model class for table "feed_category".
 *
 * @property integer $id
 * @property integer $feed_queue_id
 * @property string $title
 * @property integer $parent_id
 * @property integer $feed_id
 *
 * @property Feed $feed
 * @property FeedItem[] $feedItems
 */
class FeedCategory extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'feed_category';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'feed_queue_id', 'feed_id'], 'required'],
            [['id', 'feed_queue_id', 'parent_id', 'feed_id'], 'integer'],
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
            'id' => 'Ид категории',
            'feed_queue_id' => 'Загруженный фид',
            'title' => 'Название категории',
            'parent_id' => 'Родительская категория',
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
        return $this->hasMany(FeedItem::className(), ['feed_id' => 'feed_id', 'category_id' => 'id']);
    }
}
