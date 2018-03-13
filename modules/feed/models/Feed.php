<?php

namespace app\modules\feed\models;

use app\models\BaseModel;
use Yii;

/**
 * This is the model class for table "feed".
 *
 * @property integer $id
 * @property string $title
 * @property string $domain
 * @property string $subid
 *
 * @property FeedRedirect[] $feedRedirects
 */
class Feed extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'feed';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title', 'domain'], 'required'],
            [['title', 'domain'], 'string', 'max' => 255],
            [['subid'], 'string', 'max' => 2048]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Название фида',
            'domain' => 'Домен',
            'subid' => 'SubId'
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFeedRedirects()
    {
        return $this->hasMany(FeedRedirect::className(), ['feed_id' => 'id']);
    }
}
