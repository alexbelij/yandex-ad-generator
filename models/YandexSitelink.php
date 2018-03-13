<?php

namespace app\models;

use app\lib\LoggedInterface;
use Yii;

/**
 * This is the model class for table "yandex_sitelink".
 *
 * @property integer $id
 * @property integer $yandex_id
 * @property integer $shop_id
 * @property integer $account_id
 */
class YandexSitelink extends \yii\db\ActiveRecord implements LoggedInterface
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yandex_sitelink';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['yandex_id', 'shop_id', 'account_id'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'yandex_id' => 'Уникальный идентификатор в директе',
            'shop_id' => 'Магазин',
            'account_id' => 'Аккаунт',
        ];
    }

    /**
     * @param $shopId
     * @param $accountId
     * @param $yandexId
     * @return YandexSitelink|array|null|\yii\db\ActiveRecord
     */
    public static function createOrUpdateSiteLink($shopId, $accountId, $yandexId)
    {
        $model = self::find()
            ->andWhere([
                'shop_id' => $shopId,
                'account_id' => $accountId
            ])->one();

        if (!$model) {
            $model = new self([
                'shop_id' => $shopId,
                'account_id' => $accountId
            ]);
        }
        $model->yandex_id = $yandexId;
        $model->save();

        return $model;
    }

    /**
     * @inheritDoc
     */
    public function getEntityType()
    {
        return 'sitelink';
    }

    /**
     * @inheritDoc
     */
    public function getEntityId()
    {
        return $this->primaryKey;
    }
}
