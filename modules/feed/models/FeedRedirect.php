<?php

namespace app\modules\feed\models;

use app\models\BaseModel;
use Yii;

/**
 * This is the model class for table "feed_redirect".
 *
 * @property integer $id
 * @property integer $feed_id
 * @property string $hash_url
 * @property string $target_url
 *
 * @property Feed $feed
 */
class FeedRedirect extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'feed_redirect';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['feed_id'], 'integer'],
            [['hash_url'], 'string', 'max' => 255],
            [['target_url'], 'string', 'max' => 2048],
            [['hash_url'], 'unique'],
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
            'feed_id' => 'Фид',
            'hash_url' => 'Хэш урла',
            'target_url' => 'Урл, на который редиректить',
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
