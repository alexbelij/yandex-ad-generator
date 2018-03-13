<?php

namespace app\modules\feed\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "feed_queue".
 *
 * @property integer $id
 * @property integer $feed_id
 * @property string $created_at
 * @property string $source_file
 * @property string $result_file
 * @property string $finished_at
 * @property string $status
 * @property string $error_message
 * @property string $original_filename
 * @property integer $size
 * @property string $template
 *
 * @property Feed $feed
 */
class FeedQueue extends \yii\db\ActiveRecord
{
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESS = 'process';
    const STATUS_COMPLETED = 'completed';
    const STATUS_ERROR = 'error';

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
                    self::EVENT_BEFORE_INSERT => ['created_at']
                ]
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'feed_queue';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['feed_id'], 'integer'],
            [['created_at', 'finished_at'], 'safe'],
            [['source_file', 'result_file'], 'string', 'max' => 1024],
            [['status', 'original_filename'], 'string', 'max' => 255],
            [['error_message'], 'string', 'max' => 2048],
            [['template'], 'string'],
            [['size'], 'number'],
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
            'created_at' => 'Дата создания',
            'source_file' => 'Файл донор',
            'result_file' => 'Обработанный файл',
            'finished_at' => 'Дата завершения',
            'status' => 'Статус',
            'error_message' => 'Ошибка',
            'original_filename' => 'Название файла',
            'template' => 'Шаблон формирования фида',
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
     * @inheritDoc
     */
    public function beforeSave($insert)
    {
        if (!$this->status) {
            $this->status = self::STATUS_PENDING;
        }

        return parent::beforeSave($insert);
    }
}
