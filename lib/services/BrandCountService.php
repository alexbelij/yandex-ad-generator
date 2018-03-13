<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 17.10.16
 * Time: 15:28
 */

namespace app\lib\services;

use app\helpers\ArrayHelper;
use app\models\GeneratorSettings;
use app\models\search\ProductsSearch;
use app\models\Shop;
use yii\db\Expression;
use yii\db\Query;

/**
 * Class BrandCountService
 * @package app\lib\services
 */
class BrandCountService
{
    /**
     * @var Shop
     */
    protected $shop;

    /**
     * @var array
     */
    protected $allBrandsCount;

    /**
     * @var array
     */
    protected $brandsCountByFilter;

    /**
     * BrandCountService constructor.
     * @param Shop $shop
     */
    public function __construct(Shop $shop)
    {
        $this->shop = $shop;
    }

    /**
     * @param int $brandId
     * @return mixed
     */
    public function getCount($brandId)
    {
        $counts = $this->getAdBrandCounts();

        return ArrayHelper::getValue($counts, "$brandId.brands_count", 0);
    }

    /**
     * @param int $brandId
     * @return mixed
     */
    public function getCountByFilter($brandId)
    {
        $counts = $this->getBrandsCountByFilter();

        return ArrayHelper::getValue($counts, "$brandId.brands_count", 0);
    }

    /**
     * Возвращает количество объявлений по брендам
     *
     * @return array
     */
    protected function getAdBrandCounts()
    {
        if (is_null($this->allBrandsCount)) {

            $this->allBrandsCount = (new Query())
                ->select(['p.brand_id', new Expression('COUNT(*) as brands_count')])
                ->from('{{%ad}}')
                ->innerJoin('{{%product}} p', 'p.id = {{%ad}}.product_id')
                ->innerJoin('{{%external_product}} ep', "ep.id = p.product_id")
                ->andWhere(['p.shop_id' => $this->shop->id])
                ->groupBy('p.brand_id')
                ->indexBy('brand_id')
                ->all();
        }

        return $this->allBrandsCount;
    }

    /**
     * Возврашает массив с количеством объявлений по брендам,
     * удовлетворяющих фильтру генератора
     *
     * @return array
     */
    protected function getBrandsCountByFilter()
    {
        if (is_null($this->brandsCountByFilter)) {
            $settings = GeneratorSettings::forShop($this->shop->id);
            if (empty($settings)) {
                $this->brandsCountByFilter = $this->getAdBrandCounts();
                return $this->brandsCountByFilter;
            }

            $query = (new Query())
                ->select(['p.brand_id', new Expression('COUNT(*) as brands_count')])
                ->from('{{%ad}}')
                ->innerJoin('{{%product}} p', 'p.id = {{%ad}}.product_id')
                ->innerJoin('{{%external_product}} ep', "ep.id = p.product_id")
                ->andWhere([
                    'p.shop_id' => $this->shop->id,
                    'ep.is_available' => 1
                ])
                ->groupBy('p.brand_id')
                ->indexBy('brand_id');

            if ($settings->price_from) {
                $query->andWhere(['OR', ['>=', 'ep.price', $settings->price_from], 'ep.price IS NULL']);
            }

            if ($settings->price_to) {
                $query->andWhere(['OR', ['<=', 'ep.price', $settings->price_to], 'ep.price IS NULL']);
            }

            if ($settings->getCategoryIds()) {
                $query
                    ->andWhere(['OR',
                        ['p.category_id' => $settings->getCategoryIds()],
                        'p.category_id IS NULL'
                    ]);
            }

            $this->brandsCountByFilter = $query->all();
        }

        return $this->brandsCountByFilter;
    }
}
