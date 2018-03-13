<?php

namespace app\models;

use app\lib\tasks\SitelinksUpdateTask;
use Yii;

/**
 * This is the model class for table "sitelinks".
 *
 * @property integer $id
 * @property integer $shop_id
 * @property string $title
 *
 * @property Shop $shop
 * @property SitelinksItem[] $sitelinksItems
 */
class Sitelinks extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sitelink';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['shop_id'], 'integer'],
            ['title', 'string']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'shop_id' => 'Магазин',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShop()
    {
        return $this->hasOne(Shop::className(), ['id' => 'shop_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSitelinksItems()
    {
        return $this->hasMany(SitelinksItem::className(), ['sitelink_id' => 'id']);
    }

    /**
     * @inheritDoc
     */
    public function afterDelete()
    {
        parent::afterDelete();
        if (!TaskQueue::hasActiveTasks($this->shop_id, SitelinksUpdateTask::TASK_NAME)) {
            return TaskQueue::createNewTask($this->shop_id, 'sitelinksUpdate');
        } else {
            return null;
        }
    }
}
