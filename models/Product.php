<?php

namespace app\models;

use app\components\FileLogger;
use app\helpers\ArrayHelper;
use app\lib\api\shop\models\ExtProduct;
use app\lib\LoggedInterface;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "{{%products}}".
 *
 * @property integer $id
 * @property integer $shop_id
 * @property int $brand_id
 * @property string $product_id
 * @property string $title
 * @property int $price
 * @property int $manual_price
 * @property bool $is_available
 * @property int $yandex_sitelink_id
 * @property string $updated_at
 * @property int $category_id
 * @property bool $is_duplicate
 *
 * @property Shop $shop
 * @property Ad[] $ads
 * @property ExternalProduct $externalProduct
 */
class Product extends BaseModel implements LoggedInterface
{
    /**
     * @var ExtProduct
     */
    protected $shopProduct;

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
    public static function tableName()
    {
        return '{{%product}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['shop_id', 'product_id'], 'required'],
            [['updated_at'], 'safe'],
            [['shop_id', 'brand_id', 'yandex_sitelink_id'], 'integer'],
            ['is_available', 'boolean'],
            ['is_available', 'default', 'value' => false],
            [['title'], 'string'],
            [['price', 'manual_price'], 'integer', 'max' => 4294967295],
            [['category_id'], 'integer'],
            [['is_duplicate'], 'boolean']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ИД',
            'shop_id' => 'ИД магазина',
            'product_id' => 'Товар',
            'title' => 'Название',
            'price' => 'Цена'
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
     * Цена на товар установленна вручную
     * 
     * @return bool
     */
    public function isManualPrice()
    {
        return $this->manual_price > 0;
    }

    /**
     * @return bool
     */
    public function isAutomaticPrice()
    {
        return !$this->isManualPrice();
    }


    /**
     * @inheritDoc
     */
    public function getEntityType()
    {
        return 'product';
    }

    /**
     * @inheritDoc
     */
    public function getEntityId()
    {
        return $this->id;
    }

    /**
     * @inheritDoc
     */
    public function beforeSave($insert)
    {
        if ($insert) {
            $this->updated_at = date(self::DATETIME_FORMAT);
        } else {
            if ($this->getOldAttribute('price') != $this->price
                || $this->getOldAttribute('title') != $this->title
                || $this->getOldAttribute('manual_price') != $this->manual_price
            ) {
                $this->updated_at = date(self::DATETIME_FORMAT);
            }
        }

        return parent::beforeSave($insert);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAds()
    {
        return $this->hasMany(Ad::className(), ['product_id' => 'id'])
            ->andWhere('is_deleted = 0');
    }

    /**
     * Имеются не загруженные в яндекс объявления
     *
     * @return bool
     */
    public function hasNotLoadedAds()
    {
        /** @var Ad[] $ads */
        $ads = $this->ads;
        if (empty($ads)) {
            return false;
        }

        foreach ($ads as $ad) {
            if (empty($ad->yandex_ad_id)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getExternalProduct()
    {
        return $this->hasOne(ExternalProduct::className(), ['id' => 'product_id']);
    }

    /**
     * @return array
     */
    public function getBrandTitle()
    {
        return ArrayHelper::getValue($this->externalProduct, 'brand.title');
    }
}
