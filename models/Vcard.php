<?php

namespace app\models;

use app\lib\LoggedInterface;
use app\lib\tasks\VCardsUpdateTask;
use app\models\query\VcardsQuery;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "{{%vcards}}".
 *
 * @property integer $id
 * @property integer $shop_id
 * @property string $country
 * @property string $city
 * @property string $company_name
 * @property string $work_time
 * @property string $phone_country_code
 * @property string $phone_city_code
 * @property string $phone_number
 * @property string $phone_extension
 * @property string $street
 * @property string $house
 * @property string $building
 * @property string $apartment
 * @property string $extra_message
 * @property string $contact_email
 * @property string $ogrn
 * @property string $contact_person
 * @property string $updated_at
 * @property string $created_at
 *
 * @property Shop $shop
 */
class Vcard extends \yii\db\ActiveRecord implements LoggedInterface
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%vcard}}';
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
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['shop_id'], 'integer'],
            [['country'], 'string', 'max' => 50],
            ['phone_country_code', 'filterCountryCode'],
            [['city', 'street'], 'string', 'max' => 55],
            [['company_name', 'work_time', 'phone_country_code', 'phone_city_code', 'phone_number', 'phone_extension', 'contact_email', 'ogrn'], 'string', 'max' => 255],
            [['house'], 'string', 'max' => 30],
            [['building'], 'string', 'max' => 10],
            [['apartment'], 'string', 'max' => 100],
            [['extra_message'], 'string', 'max' => 200],
            [['contact_person'], 'string', 'max' => 155],
            [['shop_id', 'country', 'city', 'company_name', 'work_time', 'phone_country_code', 'phone_city_code', 'phone_number'], 'required'],
            ['work_time', 'validateWorkTime'],
            [['created_at', 'updated_at'], 'safe']
        ];
    }

    /**
     * Добавляем + перед кодом страны если он отстутствует
     *
     * @param $attribute
     * @param $params
     */
    public function filterCountryCode($attribute, $params)
    {
        if (substr($this->$attribute, 0, 1) !== '+') {
            $this->$attribute = '+' . $this->$attribute;
        }
    }

    public function validateWorkTime($attribute, $params)
    {
        if (preg_match('#[^\d;]#', $this->$attribute)) {
            $this->addError($attribute, 'Рабочее время задано в неверном формате');
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'shop_id' => 'Магазин',
            'country' => 'Страна',
            'city' => 'Город',
            'company_name' => 'Название компании',
            'work_time' => 'Рабочее время',
            'phone_country_code' => 'Телефон(код страны)',
            'phone_city_code' => 'Телефон(код города)',
            'phone_number' => 'Номер телефона',
            'phone_extension' => 'Добавочный номер',
            'street' => 'Улица',
            'house' => 'Дом',
            'building' => 'Номер строения или корпуса',
            'apartment' => 'Номер квартиры или офиса',
            'extra_message' => 'Дополнительная информация о рекламируемом товаре или услуге',
            'contact_email' => 'Email',
            'ogrn' => 'ОГРН',
            'contact_person' => 'Контактное лицо',
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
     * @inheritdoc
     * @return VcardsQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new VcardsQuery(get_called_class());
    }

    /**
     * @inheritdoc
     */
    public function beforeSave($insert)
    {
        if ($insert) {
            $this->updated_at = date('Y-m-d H:i:s');
        } else {
            $attributes = $this->attributes();
            $excludedAttributes = ['created_at', 'updated_at'];
            $attributes = array_diff($attributes, $excludedAttributes);
            foreach ($attributes as $attrName) {
                if ($this->getOldAttribute($attrName) != $this->$attrName) {
                    $this->updated_at = date('Y-m-d H:i:s');
                    break;
                }
            }
        }

        return parent::beforeSave($insert);
    }

    /**
     * @inheritDoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        if (array_key_exists('updated_at', $changedAttributes)) {
            TaskQueue::createNewTask($this->shop_id, VCardsUpdateTask::TASK_NAME);
        }
        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * @inheritDoc
     */
    public function getEntityType()
    {
        return 'vcard';
    }

    /**
     * @inheritDoc
     */
    public function getEntityId()
    {
        return $this->primaryKey;
    }
}
