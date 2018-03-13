<?php

namespace app\models;

use app\lib\tasks\SitelinksUpdateTask;
use Yii;

/**
 * This is the model class for table "sitelinks_item".
 *
 * @property integer $id
 * @property integer $sitelink_id
 * @property string $title
 * @property string $href
 * @property string $description
 *
 * @property Sitelinks $sitelink
 */
class SitelinksItem extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sitelink_item';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sitelink_id'], 'integer'],
            [['title'], 'string', 'max' => 30],
            [['href'], 'string', 'max' => 1024],
            [['description'], 'string', 'max' => 60]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'sitelink_id' => 'Sitelink ID',
            'title' => 'Title',
            'href' => 'Href',
            'description' => 'Description',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSitelink()
    {
        return $this->hasOne(Sitelinks::className(), ['id' => 'sitelink_id']);
    }

    /**
     * @inheritDoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        if (array_key_exists('title', $changedAttributes) ||
            array_key_exists('href', $changedAttributes)
        ) {
            $this->createUpdateSitelinksTask();
        }
        parent::afterSave($insert, $changedAttributes);
    }

    public function afterDelete()
    {
        parent::afterDelete();
        $this->createUpdateSitelinksTask();
    }

    /**
     * Создание нового таска на обновление быстрых ссылок
     */
    public function createUpdateSitelinksTask()
    {
        if (!TaskQueue::hasActiveTasks($this->sitelink->shop_id, SitelinksUpdateTask::TASK_NAME)) {
            return TaskQueue::createNewTask($this->sitelink->shop_id, 'sitelinksUpdate');
        } else {
            return null;
        }
    }

}
