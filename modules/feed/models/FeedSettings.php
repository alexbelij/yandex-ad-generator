<?php

namespace app\modules\feed\models;

use Yii;

/**
 * This is the model class for table "feed_settings".
 *
 * @property integer $id
 * @property integer $feed_id
 * @property string $title
 * @property string $settings
 *
 * @property Feed $feedQueue
 */
class FeedSettings extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'feed_settings';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['feed_id'], 'integer'],
            [['settings'], 'string'],
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
            'feed_queue_id' => 'Фид',
            'title' => 'Заголовок',
            'settings' => 'Настройки',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFeed()
    {
        return $this->hasOne(Feed::className(), ['id' => 'feed_id']);
    }
}
