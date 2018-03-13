<?php

namespace app\modules\feed\models;

use app\models\BaseModel;
use Yii;

/**
 * This is the model class for table "quick_redirect".
 *
 * @property integer $id
 * @property string $source
 * @property string $target
 */
class QuickRedirect extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'quick_redirect';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['source', 'target'], 'string', 'max' => 2048],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'source' => 'Урл, с которого редиректить',
            'target' => 'Урл, на который редиректить',
        ];
    }
}
