<?php

namespace app\models;

use app\lib\LoggedInterface;
use Faker\Provider\DateTime;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%ad}}".
 *
 * @property integer $id
 * @property integer $product_id
 * @property string $title
 * @property string $keywords
 * @property string $created_at
 * @property string $updated_at
 * @property string $generated_at
 * @property bool $is_auto
 * @property int $is_deleted
 * @property int $revision
 * @property bool $is_require_verification
 *
 * @property Product $product
 * @property AdYandexCampaign[] $yandexAds
 * @property AdKeyword[] $adKeywords
 */
class Ad extends BaseModel implements LoggedInterface
{
    const STATE_ARCHIVED = 'archived';
    const STATE_ON = 'on';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ad}}';
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
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['product_id'], 'integer'],
            [['keywords'], 'string'],
            [['title'], 'string', 'max' => 255],
            [['updated_at', 'created_at', 'generated_at'], 'safe'],
            [['is_auto', 'is_deleted', 'is_require_verification'], 'boolean']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'product_id' => 'Товар',
            'title' => 'Заголовок',
            'keywords' => 'Ключевые слова',
            'is_require_verification' => 'Требует проверки'
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(Product::className(), ['id' => 'product_id']);
    }

    /**
     * @inheritDoc
     */
    public function beforeSave($insert)
    {
        if (!$insert) {
            if ($this->getOldAttribute('title') != $this->title) {
                $this->updated_at = date(self::DATETIME_FORMAT);
            }
        } else {
            $this->updated_at = date(self::DATETIME_FORMAT);
        }

        return parent::beforeSave($insert);
    }

    /**
     * @inheritDoc
     */
    public function getEntityType()
    {
        return 'ad';
    }

    /**
     * @inheritDoc
     */
    public function getEntityId()
    {
        return $this->primaryKey;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getYandexAds()
    {
        return $this->hasMany(AdYandexCampaign::className(), ['ad_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAdKeywords()
    {
        return $this->hasMany(AdKeyword::className(), ['ad_id' => 'id']);
    }


    /**
     * Имеются ли размещенные объявления
     *
     * @return bool
     */
    public function isPublished()
    {
        $isPublished = ArrayHelper::getColumn($this->yandexAds, 'is_published');

        return array_sum($isPublished) > 0;
    }

    /**
     * Пометить объявление на удаление
     */
    public function markForDelete()
    {
        $this->is_deleted = 1;
        $this->save();
    }

    /**
     * Возвращает номер ревизии
     *
     * @param int $shopId
     * @return false|null|string
     */
    public static function getRevision($shopId)
    {
        return self::find()
            ->innerJoin(['p' => Product::tableName()], 'p.id = {{%ad}}.product_id')
            ->andWhere(['p.shop_id' => $shopId])
            ->select(new Expression('MAX(revision)'))
            ->limit(1)
            ->scalar();
    }

    /**
     * @return array
     */
    public function getKeywordsList()
    {
        return ArrayHelper::getColumn($this->adKeywords, 'keyword');
    }

    /**
     * @return array
     */
    public function getKeywordsArray()
    {
        return array_filter(array_map('trim', preg_split("#(\n|\r\n)#", $this->keywords)));
    }
}
