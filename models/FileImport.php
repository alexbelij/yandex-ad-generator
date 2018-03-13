<?php

namespace app\models;

use app\components\LoggerInterface;
use Yii;
use yii\base\Exception;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "file_upload".
 *
 * @property integer $id
 * @property string $original_filename
 * @property string $filename
 * @property string $created_at
 * @property integer $size
 * @property string $type
 * @property string $error_msg
 * @property int $shop_id
 * @property bool $is_loaded
 * @property string $company_name
 * @property string $catalog_date
 *
 * @property Shop $shop
 */
class FileImport extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'file_import';
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
                    self::EVENT_BEFORE_INSERT => ['created_at'],
                ]
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_at', 'catalog_date'], 'safe'],
            [['size', 'shop_id'], 'integer'],
            [['is_loaded'], 'boolean'],
            [['original_filename', 'filename', 'error_msg', 'company_name'], 'string', 'max' => 255],
            [['type'], 'string', 'max' => 50]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'original_filename' => 'Original Filename',
            'filename' => 'Filename',
            'created_at' => 'Created At',
            'size' => 'Size',
            'type' => 'Type',
            'error_msg' => 'Error',
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
     * @return array
     */
    public static function getStrategiesList()
    {
        return [
            Shop::PARSE_STRATEGY_DEFAULT => 'По умолчанию',
            Shop::PARSE_STRATEGY_BRAND => 'Бренд из категорий',
            Shop::PARSE_STRATEGY_DESCRIPTION => 'По умолчанию + парсинг описания'
        ];
    }
}
