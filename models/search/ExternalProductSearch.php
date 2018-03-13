<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 29.10.16
 * Time: 17:40
 */

namespace app\models\search;

use app\models\ExternalProduct;
use yii\data\ActiveDataProvider;

/**
 * Class ExternalProductSearch
 * @package app\models\search
 */
class ExternalProductSearch extends ExternalProduct
{
    /**
     * @var string
     */
    public $dateFrom;

    /**
     * @var string
     */
    public $dateTo;

    /**
     * @var int|int[]
     */
    public $outerBrandId;

    /**
     * @var int
     */
    public $priceFrom;

    /**
     * @var int
     */
    public $priceTo;

    /**
     * @var string
     */
    public $outerCategoryId;

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            [['dateFrom', 'dateTo'], 'validateDate', 'skipOnEmpty' => true],
            [['shop_id'], 'required'],
            ['title', 'trim'],
            [['brand_id', 'outerBrandId', 'title', 'outerCategoryId', 'category_id', 'original_title'], 'safe'],
            [['priceFrom', 'priceTo'], 'number'],
            [['is_available', 'is_generate_ad'], 'boolean']
        ];
    }

    /**
     * @param string $attr
     * @param array $params
     * @return bool
     */
    public function validateDate($attr, $params)
    {
        if (strtotime($this->$attr) == false) {
            $this->addError($attr, 'Неверно указана дата');
            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function afterValidate()
    {
        parent::afterValidate();
        if ($this->dateFrom) {
            $this->dateFrom = date('Y-m-d 00:00:00', strtotime($this->dateFrom));
        }

        if ($this->dateTo) {
            $this->dateTo = date('Y-m-d 23:59:59', strtotime($this->dateTo));
        }
    }

    /**
     * @param array $params
     * @return ActiveDataProvider
     */
    public function search($params = [])
    {
        $query = ExternalProduct::find()->from(['ep' => self::tableName()]);

        $this->load($params) || $this->load($params, '');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        if (!$this->validate()) {
            $query->andWhere('0=1');
            return $dataProvider;
        }

        $dataProvider->sort->attributes['brand.title'] = [
            'asc' => ['eb.title' => SORT_ASC],
            'desc' => ['eb.title' => SORT_DESC]
        ];
        $dataProvider->sort->attributes['category.title'] = [
            'asc' => ['ec.title' => SORT_ASC],
            'desc' => ['ec.title' => SORT_DESC]
        ];

        $query->leftJoin('{{external_brand}} eb', "eb.id = ep.brand_id AND eb.shop_id = :shopId", [':shopId' => $this->shop_id]);
        $query->leftJoin('{{external_category}} ec', "ec.id = ep.category_id AND ec.shop_id = :shopId", [':shopId' => $this->shop_id]);

        $query->andFilterWhere([
            'ep.shop_id' => $this->shop_id,
            'ep.brand_id' => $this->brand_id,
            'ep.category_id' => $this->category_id,
        ]);

        if ($this->is_generate_ad) {
            $query->andWhere(['ep.is_generate_ad' => true]);
        }

        if ($this->is_available) {
            $query->andWhere(['ep.is_available' => $this->is_available]);
        }

        $query
            ->andFilterWhere(['like', 'ep.title', $this->title])
            ->andFilterWhere(['like', 'ep.original_title', $this->original_title]);

        if ($this->outerBrandId) {
            $query->andWhere(['eb.outer_id' => $this->outerBrandId]);
        }

        if ($this->outerCategoryId) {
            $query->andWhere(['ec.outer_id' => $this->outerCategoryId]);
        }

        if ($this->priceFrom) {
            $query->andWhere(['>=', 'ep.price', $this->priceFrom]);
        }

        if ($this->priceTo) {
            $query->andWhere(['<=', 'ep.price', $this->priceTo]);
        }

        if ($this->dateFrom) {
            $query->andWhere(['>=', 'ep.created_at', $this->dateFrom]);
        }
        if ($this->dateTo) {
            $query->andWhere(['<=', 'ep.created_at', $this->dateTo]);
        }

        return $dataProvider;
    }
}
