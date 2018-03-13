<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 26.08.16
 * Time: 8:32
 */

namespace app\behaviors;

use app\models\ExternalEntity;
use app\models\FileImportLog;
use yii\base\Behavior;
use yii\base\Event;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * Class FileLogBehavior
 * @package app\behaviors
 */
class FileLogBehavior extends Behavior
{
    /**
     * @var string
     */
    public $entityType;

    /**
     * @inheritDoc
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'logAfterInsertOrUpdate',
            ActiveRecord::EVENT_AFTER_UPDATE => 'logAfterUpdate',
            ActiveRecord::EVENT_AFTER_DELETE => 'logAfterInsertOrUpdate'
        ];
    }

    /**
     * @param Event $event
     */
    public function logAfterInsertOrUpdate(Event $event)
    {
        /** @var ExternalEntity $model */
        $model = $event->sender;
        $operation = $event->name == ActiveRecord::EVENT_AFTER_INSERT ?
            FileImportLog::OPERATION_INSERT : FileImportLog::OPERATION_DELETE;

        $entityId = $operation == FileImportLog::OPERATION_INSERT ?
            $model->id : null;

        $fileImportLog = new FileImportLog([
            'title' => $model->title,
            'operation' => $operation,
            'entity_type' => $this->entityType,
            'entity_id' => $entityId,
            'file_import_id' => $model->getFileImportId()
        ]);
        $fileImportLog->save();
    }

    /**
     * @param Event $event
     */
    public function logAfterUpdate(Event $event)
    {
        /** @var ExternalEntity $model */
        $model = $event->sender;
        $changedAttributes = ArrayHelper::getValue($event, 'changedAttributes', []);
        $oldValues = $changedAttributes;
        $newValues = [];
        foreach (array_keys($oldValues) as $field) {
            $newValues[$field] = $model->{$field};
        }

        $fileImportLog = new FileImportLog([
            'title' => $model->title,
            'operation' => FileImportLog::OPERATION_UPDATE,
            'old_value' => json_encode($oldValues),
            'new_value' => json_encode($newValues),
            'entity_type' => $this->entityType,
            'entity_id' => $model->id,
            'file_import_id' => $model->getFileImportId()
        ]);
        $fileImportLog->save();
    }
}