<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 03.09.16
 * Time: 8:19
 */

namespace app\behaviors;
use app\models\BaseModel;
use yii\base\Behavior;
use yii\base\Event;
use yii\db\ActiveRecord;

/**
 * Работа с JSON полями в моделях
 *
 * Class JsonFieldsBehavior
 * @package common\behaviors
 * @author Denis Dyadyun <sysadm85@gmail.com>
 */
class JsonFieldsBehavior extends Behavior
{
    /**
     * @var array
     */
    public $fields = [];

    /**
     * @inheritDoc
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_FIND => 'fromJsonToArray',
            ActiveRecord::EVENT_BEFORE_INSERT => 'fromArrayToJson',
            ActiveRecord::EVENT_BEFORE_UPDATE => 'fromArrayToJson',
            ActiveRecord::EVENT_AFTER_INSERT => 'fromJsonToArray',
            ActiveRecord::EVENT_AFTER_UPDATE => 'fromJsonToArray'
        ];
    }

    /**
     * @param Event $event
     */
    public function fromJsonToArray(Event $event)
    {
        /** @var BaseModel $model */
        $model = $event->sender;
        foreach ($this->fields as $field) {
            if (empty($model->$field)) {
                $model->$field = [];
            } else {
                $model->$field = json_decode($model->$field, true);
            }
        }
    }

    /**
     * @param Event $event
     */
    public function fromArrayToJson(Event $event)
    {
        /** @var BaseModel $model */
        $model = $event->sender;
        foreach ($this->fields as $field) {
            if (!empty($model->$field) && is_array($model->$field)) {
                $model->$field = json_encode($model->$field);
            } else {
                $model->$field = null;
            }
        }
    }
}