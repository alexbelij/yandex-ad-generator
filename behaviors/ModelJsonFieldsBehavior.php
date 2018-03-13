<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 15.10.16
 * Time: 9:59
 */

namespace app\behaviors;

use app\helpers\ArrayHelper;
use yii\base\Behavior;
use yii\base\Event;
use yii\db\ActiveRecord;
use yii\helpers\Inflector;

/**
 * Поведение для маппинга данных, содержащихся в json поле в поля модели
 *
 * Class ModelJsonFieldsBehavior
 * @package app\behaviors
 */
class ModelJsonFieldsBehavior extends Behavior
{
    /**
     * Поле, которое содержит данные поля модели
     *
     * @var array
     */
    public $field;

    /**
     * @var array
     */
    public $modelFields = [];

    /**
     * @inheritDoc
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_FIND => 'fromJsonToModelFields',
            ActiveRecord::EVENT_BEFORE_UPDATE => 'fromModelFieldsToJson',
            ActiveRecord::EVENT_BEFORE_INSERT => 'fromModelFieldsToJson'
        ];
    }

    /**
     * @param Event $event
     */
    public function fromJsonToModelFields(Event $event)
    {
        /** @var ActiveRecord $model */
        $model = $event->sender;
        $data = json_decode($model->{$this->field}, true);
        if (!$data) {
            return;
        }
        foreach ($this->modelFields as $key => $field) {
            if (is_numeric($key)) {
                $sourceField = $field;
                $targetField = Inflector::underscore($field);
            } else {
                $sourceField = $key;
                $targetField = $field;
            }
            $model->$sourceField = ArrayHelper::getValue($data, $targetField);
        }
    }

    /**
     * @param Event $event
     */
    public function fromModelFieldsToJson(Event $event)
    {
        /** @var ActiveRecord $model */
        $model = $event->sender;
        $data = [];
        foreach ($this->modelFields as $key => $field) {
            if (is_numeric($key)) {
                $sourceField = $field;
                $targetField = Inflector::underscore($field);
            } else {
                $sourceField = $key;
                $targetField = $field;
            }
            $data[$targetField] = ArrayHelper::getValue($model, $sourceField);
        }
        $model->{$this->field} = json_encode($data);
    }
}
