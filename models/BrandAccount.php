<?php

namespace app\models;

use app\helpers\ArrayHelper;
use Yii;

/**
 * This is the model class for table "brand_account".
 *
 * @property integer $id
 * @property integer $brand_id
 * @property integer $account_id
 * @property integer $shop_id
 *
 * @property Account $account
 * @property Shop $shop
 */
class BrandAccount extends \yii\db\ActiveRecord
{
    /**
     * @var string
     */
    public $brandTitle;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'brand_account';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['brand_id', 'account_id', 'shop_id'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'brand_id' => 'Бренд',
            'account_id' => 'Аккаунт',
            'shop_id' => 'Магазин',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAccount()
    {
        return $this->hasOne(Account::className(), ['id' => 'account_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShop()
    {
        return $this->hasOne(Shop::className(), ['id' => 'shop_id']);
    }

    /**
     * @param Shop $shop
     * @param int $brandId
     * @return Account
     */
    public static function getAccountByBrand(Shop $shop, $brandId)
    {
        static $cache = [];
        $key = $shop->id . '_' . $brandId;

        if (!array_key_exists($key, $cache)) {
            /** @var BrandAccount $model */
            $cache[$key] = self::find()
                ->joinWith('account')
                ->andWhere([
                    'shop_id' => $shop->id,
                    'brand_id' => $brandId
                ])->one();
        }

        $model = $cache[$key];

        return $model ? $model->account : $shop->account;
    }

    /**
     * @inheritDoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        if (!empty($changedAttributes['account_id'])) {
            $sql = 'DELETE ayc.* FROM ad_yandex_campaign ayc
                INNER JOIN ad ON ayc.ad_id = ad.id
                INNER JOIN product p ON p.id = ad.product_id
                WHERE p.brand_id = :brandId AND p.shop_id = :shopId AND ayc.account_id = :accountId';

            \Yii::$app->db->createCommand($sql, [
                ':brandId' => $this->brand_id,
                ':shopId' => $this->shop_id,
                ':accountId' => $changedAttributes['account_id']
            ])->execute();
        }
    }
}
