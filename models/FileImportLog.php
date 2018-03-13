<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "file_import_log".
 *
 * @property integer $id
 * @property integer $file_import_id
 * @property string $title
 * @property string $operation
 * @property string $old_value
 * @property string $new_value
 * @property string $entity_type
 * @property integer $entity_id
 *
 * @property FileImport $fileImport
 */
class FileImportLog extends BaseModel
{
    const OPERATION_INSERT = 'insert';
    const OPERATION_UPDATE = 'update';
    const OPERATION_DELETE = 'delete';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'file_import_log';
    }

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
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['file_import_id', 'entity_id'], 'integer'],
            [['old_value', 'new_value'], 'string'],
            [['title'], 'string', 'max' => 250],
            [['operation'], 'string', 'max' => 50],
            [['entity_type'], 'string', 'max' => 20]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'file_import_id' => 'File Import ID',
            'title' => 'Title',
            'operation' => 'Operation',
            'diff' => 'Diff',
            'entity_type' => 'Entity Type',
            'entity_id' => 'Entity ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFileImport()
    {
        return $this->hasOne(FileImport::className(), ['id' => 'file_import_id']);
    }
}
