<?php

namespace app\models\search;

use app\models\Ad;
use app\models\ExternalProduct;
use app\models\Shop;
use yii\base\InvalidParamException;
use yii\base\Model;
use yii\db\ActiveQuery;
use yii\db\Expression;

/**
 * Класс поиска объявлений для обновления в директе
 *
 * Class YandexUpdateSearch
 * @package app\models\search
 */
class YandexAdUpdateSearch extends Model
{
    /**
     * @var Shop
     */
    public $shop;

    /**
     * @var int[]
     */
    public $brandIds;

    /**
     * @var float
     */
    public $priceFrom;

    /**
     * @var float
     */
    public $priceTo;

    /**
     * @var array
     */
    public $categoryIds;

    /**
     * @var int
     */
    public $accountId;

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            ['shop', 'required'],
            [['shop', 'brandIds', 'priceFrom', 'priceTo', 'categoryIds'], 'safe'],
            [['accountId'], 'integer'],
        ];
    }

    /**
     * @param array $params
     * @return ActiveQuery
     */
    public function search(array $params = [])
    {
        $this->load($params) || $this->load($params, '');

        if (!$this->validate()) {
            $errors = $this->getErrors();
            throw new InvalidParamException(reset($errors));
        }

        $sortValExpr = new Expression("
            (CASE 
                WHEN 1 < (SELECT count(*) from ad_yandex_campaign yc WHERE yc.ad_id = ad.id AND yc.yandex_ad_id is NULL) THEN 500
                WHEN {{%ad}}.is_deleted = 1 OR ({{%product}}.is_available = 1 AND (ep.is_available = 0 OR ep.is_available IS NULL)) THEN 0
                WHEN {{%product}}.is_available = 0 AND (ep.is_available = 1 OR ep.is_available IS NULL) THEN 1
                WHEN {{%product}}.is_available = 1 AND ep.price IS NOT NULL AND ep.price != {{%product}}.price THEN 2
                ELSE 3
            END ) as ad_sort_val
        ");

        $query = Ad::find()
            ->select(['{{%ad}}.*', $sortValExpr])
            ->innerJoinWith(['product'])
            ->leftJoin(['ep' => ExternalProduct::tableName()], "ep.id = {{%product}}.product_id AND ep.shop_id = :shopId")
            ->leftJoin(['ec' => ExternalProduct::tableName()], "ec.id = ep.category_id AND ec.shop_id = :shopId")
            ->andFilterWhere([
                '{{%product}}.shop_id' => $this->shop->id,
                '{{%product}}.brand_id' => $this->brandIds,
            ])
            ->andWhere('ep.is_available > 0 OR EXISTS (SELECT * FROM ad_yandex_campaign where ad_id = {{%ad}}.id AND yandex_ad_id IS NOT NULL)')
            ->andWhere(['{{%product}}.is_duplicate' => 0]);

        $query->addParams([':shopId' => $this->shop->id]);

        if ($this->priceFrom) {
            $query->andWhere(['OR', ['>=', 'ep.price', $this->priceFrom], 'ep.price IS NULL']);
        }

        if ($this->priceTo) {
            $query->andWhere(['OR', ['<=', 'ep.price', $this->priceTo], 'ep.price IS NULL']);
        }

        if ($this->accountId) {
            $query->leftJoin('brand_account ba', 'ba.brand_id = {{%product}}.brand_id');
            //используется дефолтный аккаунта магазина для обновления
            if ($this->shop->account_id == $this->accountId) {
                $query->andWhere(['OR', ['ba.account_id' => $this->accountId], 'ba.account_id IS NULL']);
            } else {
                $query->andWhere(['ba.account_id' => $this->accountId]);
            }
        }

        if ($this->categoryIds) {
            if ($this->shop->isFileLoadStrategy()) {
                $query
                    ->andWhere(['OR',
                        ['{{%ep}}.category_id' => $this->categoryIds],
                        '{{%ep}}.category_id IS NULL'
                    ]);
            } else {
                $query
                    ->andWhere(['OR',
                        ['{{%ec}}.outer_id' => $this->categoryIds],
                        '{{%ec}}.outer_id IS NULL'
                    ]);
            }
        }
        $query->orderBy('ad_sort_val');

        return $query;
    }
}
