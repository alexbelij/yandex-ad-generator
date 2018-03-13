<?php

namespace app\models;

use app\lib\api\yandex\direct\entity\Campaign;
use Yii;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "yandex_update_log".
 *
 * @property integer $id
 * @property integer $task_id
 * @property integer $shop_id
 * @property string $entity_type
 * @property integer $entity_id
 * @property string $created_at
 * @property string $status
 * @property string $message
 * @property string $operation
 * @property string $points
 *
 * @property Shop $shop
 * @property Product $product
 * @property Campaign $campaign
 * @property AdYandexCampaign $ad
 */
class YandexUpdateLog extends \yii\db\ActiveRecord
{
    const STATUS_SUCCESS = 'success';
    const STATUS_ERROR = 'error';

    const OPERATION_CREATE = 'create';
    const OPERATION_UPDATE = 'update';
    const OPERATION_REMOVE = 'remove';
    const OPERATION_API_LOAD = 'load_from_api';
    const OPERATION_RESUME = 'resume';
    const OPERATION_KEYWORDS_CREATE = 'keywords_create';
    const OPERATION_KEYWORDS_UPDATE = 'keywords_update';
    const OPERATION_KEYWORDS_MOVE = 'keywords_move';
    const OPERATION_UPDATE_BIDS = 'update_bids';
    const OPERATION_VCARD_CREATE = 'create_vcard';
    const OPERATION_SITELINKS_UPDATE = 'sitelinks_update';
    const OPERATION_GROUP_RARELY_SERVED = 'rarely_served';
    const OPERATION_INFO = 'info';
    
    const OPERATION_CREATE_SITELINKS = 'create_sitelinks';

    const ENTITY_AD = 'YandexAd';
    const ENTITY_CAMPAIGN = 'campaign';
    const ENTITY_VCARD = 'vcard';
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yandex_update_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['task_id', 'shop_id', 'entity_type', 'status'], 'required'],
            [['task_id', 'shop_id', 'entity_id', 'points'], 'integer'],
            [['created_at'], 'safe'],
            [['entity_type', 'status'], 'string', 'max' => 50],
            [['message', 'operation'], 'string', 'max' => 3000]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'task_id' => 'Задача',
            'shop_id' => 'Магазин',
            'entity_type' => 'Тип сущности',
            'entity_id' => 'Сущность',
            'created_at' => 'Дата создания',
            'status' => 'Статус',
            'message' => 'Сообщение',
            'points' => 'Списано баллов'
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
     * @return ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(Product::className(), ['id' => 'entity_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getCampaign()
    {
        return $this->hasOne(YandexCampaign::className(), ['id' => 'entity_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getAd()
    {
        return $this->hasOne(AdYandexCampaign::className(), ['id' => 'entity_id']);
    }

    /**
     * Возвращает название сущности
     *
     * @return string|int
     */
    public function getEntityTitle()
    {
        $entityMap = self::getEntityTitleMap();
        if (array_key_exists($this->entity_type, $entityMap)) {
            $modelClass = $entityMap[$this->entity_type]['model'];
            $field = $entityMap[$this->entity_type]['field'];

            $model = $modelClass::findOne($this->entity_id);

            return $model ? ArrayHelper::getValue($model, $field) : $this->entity_id;
        }

        return $this->entity_id;
    }

    protected static function getEntityTitleMap()
    {
        return [
            self::ENTITY_AD => [
                'model' => AdYandexCampaign::className(),
                'field' => 'ad.title'
            ],
            self::ENTITY_CAMPAIGN => [
                'model' => YandexCampaign::className(),
                'field' => 'title'
            ],
            self::ENTITY_VCARD => [
                'model' => Vcard::className(),
                'field' => 'company_name'
            ]
        ];
    }

    /**
     * Если в логе ошибки
     *
     * @param TaskQueue $task
     * @return bool
     */
    public static function hasError(TaskQueue $task)
    {
        return self::find()
            ->andWhere(['task_id' => $task->id, 'status' => self::STATUS_ERROR])
            ->exists();
    }
}
