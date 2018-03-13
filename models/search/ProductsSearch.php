<?php

namespace app\models\search;

use app\helpers\TreeHelper;
use app\lib\api\shop\gateways\CategoriesGateway;
use app\lib\provider\ExternalProductProvider;
use app\lib\provider\processStrategies\ProductsAfterLoadStrategy;
use app\models\Product;
use app\models\Shop;
use yii\base\InvalidParamException;
use yii\base\Model;
use yii\data\BaseDataProvider;
use yii\db\Expression;
use yii\db\Query;

/**
 * Class ProductsApiSearch
 * @package app\models\search
 * @author Denis Dyadyun <sysadm85@gmail.com>
 */
class ProductsSearch extends Model
{
    const DATA_FILTER_TYPE_AD = 'ad';
    const DATA_FILTER_TYPE_PRODUCT = 'product';

    /**
     * Ид товара из таблицы {{%product}}
     * @var int
     */
    public $id;

    /**
     * @var int
     */
    public $brandId;

    /**
     * @var int
     */
    public $shopId;

    /**
     * @var bool
     */
    public $onlyActive = true;

    /**
     * @var array
     */
    public $defaultBrandIds = [];

    /**
     * @var float
     */
    public $priceFrom;

    /**
     * @var float
     */
    public $priceTo;

    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $adTitle;

    /**
     * @var int|int[]
     */
    public $categoryId;

    /**
     * @var bool
     */
    public $isRequireVerification;

    /**
     * @var string
     */
    public $dateFrom;

    /**
     * @var string
     */
    public $dateTo;

    /**
     * @var bool
     */
    public $withoutAd = false;

    /**
     * @var bool
     */
    public $isGenerateAd = false;

    /**
     * @var string
     */
    public $dateFilterType = self::DATA_FILTER_TYPE_AD;

    /**
     * @var Shop
     */
    private $shop;

    /**
     * Поиск товаров только с ключевыми фразами
     *
     * @var bool
     */
    public $onlyWithKeywords = false;

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            [['title', 'adTitle'], 'trim'],
            [['brandId', 'shopId', 'id', 'categoryId', 'dateFrom', 'dateTo'], 'safe'],
            [['onlyActive', 'isRequireVerification', 'withoutAd', 'isGenerateAd'], 'boolean'],
            ['shopId', 'required'],
            [['priceFrom', 'priceTo'], 'number'],
            [['title', 'adTitle', 'dateFilterType'], 'string'],
            [['dateFrom', 'dateTo'], 'validateDate'],
            [['onlyWithKeywords'], 'boolean'],
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
    public function attributeLabels()
    {
        return [
            'isRequireVerification' => 'Требует проверки',
            'dateFrom' => 'C',
            'dateTo' => 'по'
        ];
    }

    /**
     * @param array $params
     * @return BaseDataProvider
     */
    public function search($params = [])
    {
        if (!empty($params)) {
            $this->load($params) || $this->load($params, '');
        }

        if (!$this->validate()) {
            throw new InvalidParamException();
        }

        return $this->getExternalProductProvider();
    }

    /**
     * @param Shop $shop
     * @return \yii\data\ActiveDataProvider
     */
    protected function searchExternalProduct(Shop $shop)
    {
        $searchModel = new ExternalProductSearch();

        if (empty($this->brandId) && !empty($this->defaultBrandIds)) {
            $brandIds = $this->defaultBrandIds;
        } else {
            $brandIds = $this->brandId;
        }

        $searchData = [
            'priceFrom' => $this->priceFrom,
            'priceTo' => $this->priceTo,
            'is_available' => $this->onlyActive,
            'shop_id' => $this->shopId,
            'title' => $this->title,
            'is_generate_ad' => $this->isGenerateAd
        ];

        if ($this->dateFilterType == self::DATA_FILTER_TYPE_PRODUCT) {
            $searchData['dateFrom'] = $this->dateFrom;
            $searchData['dateTo'] = $this->dateTo;
        }

        if (is_string($this->categoryId) && false !== strpos($this->categoryId, ',')) {
            $this->categoryId = explode(',', $this->categoryId);
        }


        if ($shop->isFileLoadStrategy()) {
            $searchData['category_id'] = $this->categoryId;
            $searchData['brand_id'] = $brandIds;
        } else {
            $searchData['outerCategoryId'] = $this->categoryId;
            $searchData['outerBrandId'] = $brandIds;
        }

        return $searchModel->search($searchData);
    }

    /**
     * @return ExternalProductProvider
     */
    protected function getExternalProductProvider()
    {
        /** @var Shop $shop */
        $shop = Shop::findOne($this->shopId);

        $dataProvider = $this->searchExternalProduct($shop);

        /** @var Query $query */
        $query = $dataProvider->query;

        $query
            ->distinct()
            ->leftJoin(['p' => Product::tableName()], "p.product_id = ep.id AND p.shop_id=:shopId")
            ->leftJoin('ad', 'ad.product_id = p.id AND ad.is_deleted = false');

        //$query->andWhere('ad.is_deleted IS NOT NULL');

        if ($this->adTitle) {
            $query->andWhere(['like', 'ad.title', $this->adTitle]);
        }

        if ($this->isRequireVerification) {
            $query->andWhere(['ad.is_require_verification' => true]);
        }

        if ($this->dateFilterType == self::DATA_FILTER_TYPE_AD) {
            if ($this->dateFrom) {
                $query->andWhere(['>=', 'ad.generated_at', date('Y-m-d 00:00:00', strtotime($this->dateFrom))]);
            }
            if ($this->dateTo) {
                $query->andWhere(['<=', 'ad.generated_at', date('Y-m-d 23:59:59', strtotime($this->dateTo))]);
            }
        }

        if ($this->id) {
            $subQuery = (new Query())
                ->select(new Expression('1'))
                ->from(['p' => Product::tableName()])
                ->andWhere("p.product_id = ep.id")
                ->andWhere(['p.id' => $this->id]);
            $query->andWhere(['EXISTS', $subQuery]);
        }

        if ($this->withoutAd) {
            $query
                ->andWhere('TRIM(ad.title) = \'\' OR ad.title IS NULL OR TRIM(ad.keywords) = \'\'')
                ->andWhere(['OR', ['p.is_duplicate' => 0], ['p.is_duplicate' => null]]);

        }

        $query->addParams([':shopId' => $shop->id]);

        if ($this->onlyWithKeywords) {
            $keywordsCountSubQuery = (new Query())
                ->select(new Expression('count(*)'))
                ->from('ad_keyword')
                ->andWhere('ad.id = ad_keyword.ad_id');

            $query->andWhere('0 < (' . $keywordsCountSubQuery->createCommand()->getRawSql() . ')');
        }

        //echo $query->createCommand()->getRawSql();die;

        return new ExternalProductProvider([
            'query' => $query,
            'key' => 'id',
            'shop' => $shop,
            'processStrategy' => new ProductsAfterLoadStrategy(['shopId' => $this->shopId])
        ]);
    }

    /**
     * Возвращает имя столбца, по которому осуществляется связь таблиц
     *  product.product_id -> external_product.{имя столбца}
     *
     * @param Shop $shop
     * @return string
     */
    public static function getProductJoinField(Shop $shop)
    {
        return $shop->external_strategy == Shop::EXTERNAL_STRATEGY_API ? 'outer_id' : 'id';
    }

    /**
     * @return Shop
     */
    public function getShop()
    {
        if (is_null($this->shop)) {
            $this->shop = Shop::findOne($this->shopId);
        }

        return $this->shop;
    }
}
