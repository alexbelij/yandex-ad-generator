<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 03.09.16
 * Time: 8:59
 */

namespace app\traits;

use app\models\BaseModel;
use yii\base\Event;
use yii\base\Model;
use yii\db\ActiveRecord;

trait ModelFieldTrait
{
    /**
     * @return array
     */
    abstract protected function getModelFields();

    /**
     * Инициализация трейта
     */
    public function initModelTrait()
    {
        $this->on(ActiveRecord::EVENT_AFTER_FIND, [$this, 'fromArrayToModel']);
        $this->on(ActiveRecord::EVENT_BEFORE_UPDATE, [$this, 'fromModelToArray']);
        $this->on(ActiveRecord::EVENT_BEFORE_INSERT, [$this, 'fromModelToArray']);
    }

    /**
     * Инициализация модели
     */
    public function init()
    {
        $this->initModelTrait();
    }


    /**
     * @param Event $event
     */
    public function fromArrayToModel(Event $event)
    {
        /** @var BaseModel $model */
        $model = $event->sender;
        foreach ($this->getModelFields() as $field => $modelClass) {
            if (empty($model->$field)) {
                $model->$field = new $modelClass;
            } else {
                $model->$field = new $modelClass(json_decode($model->$field, true));
            }
        }
    }

    /**
     * @param Event $event
     */
    public function fromModelToArray(Event $event)
    {
        /** @var BaseModel $model */
        $model = $event->sender;
        foreach ($this->getModelFields() as $field) {
            if (!empty($model->$field) && $model->$field instanceof Model) {
                $model->$field = $model->toArray();
            }
        }
    }

    /**
     * Sets the attribute values in a massive way.
     * @param array $values attribute values (name => value) to be assigned to the model.
     * @param boolean $safeOnly whether the assignments should only be done to the safe attributes.
     * A safe attribute is one that is associated with a validation rule in the current [[scenario]].
     * @see safeAttributes()
     * @see attributes()
     */
    public function setAttributes($values, $safeOnly = true)
    {
        $modelFields = $this->getModelFields();
        if (is_array($values)) {
            $attributes = array_flip($safeOnly ? $this->safeAttributes() : $this->attributes());
            foreach ($values as $name => $value) {
                if (isset($attributes[$name])) {
                    if (array_key_exists($name, $modelFields)) {
                        $modelClass = $modelFields[$name]['model'];
                        $value = new $modelClass($value);
                    }
                    $this->$name = $value;
                } elseif ($safeOnly) {
                    $this->onUnsafeAttribute($name, $value);
                }
            }
        }
    }
}