<?php

namespace app\models;

use app\lib\LoggedInterface;
use Yii;

/**
 * This is the model class for table "yandex_campaign".
 *
 * @property integer $id
 * @property integer $shop_id
 * @property integer $brand_id
 * @property string $title
 * @property integer $yandex_id
 * @property int $products_count
 * @property int $yandex_vcard_id
 * @property int $campaign_template_id
 * @property int $account_id
 *
 * @property Shop $shop
 * @property CampaignTemplate $campaignTemplate
 * @property Account $account
 */
class YandexCampaign extends BaseModel implements LoggedInterface
{
    const MAX_CAMPAIGN_PRODUCTS = 980;

    /**
     * @var array
     */
    public $yandexData = [];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yandex_campaign';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['shop_id', 'brand_id', 'title', 'yandex_id', 'campaign_template_id'], 'required'],
            [['shop_id', 'brand_id', 'yandex_id', 'yandex_vcard_id', 'campaign_template_id', 'account_id'], 'integer'],
            ['products_count', 'integer'],
            [['title'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'shop_id' => 'Магазин',
            'brand_id' => 'Бренд',
            'title' => 'Название кампании',
            'yandex_id' => 'Yandex ID',
            'products_count' => 'Товаров в кампании',
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
     * Обновление количества товаров в кампании
     *
     * @return int
     */
    public function incrementProductsCount()
    {
        return self::updateAllCounters(['products_count' => 1], ['id' => $this->id]);
    }

    /**
     * Уменьшить счетчик кол-ва товаров в кампании
     * @return int
     */
    public function decrementProductsCount()
    {
        return self::updateAllCounters(['products_count' => -1], ['id' => $this->id]);
    }

    /**
     * @inheritDoc
     */
    public function getEntityType()
    {
        return 'campaign';
    }

    /**
     * @inheritDoc
     */
    public function getEntityId()
    {
        return $this->id;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCampaignTemplate()
    {
        return $this->hasOne(CampaignTemplate::className(), ['id' => 'campaign_template_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAccount()
    {
        return $this->hasOne(Account::className(), ['id' => 'account_id']);
    }
}
