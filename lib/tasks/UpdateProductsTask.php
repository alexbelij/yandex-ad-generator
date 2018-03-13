<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 29.10.16
 * Time: 9:12
 */

namespace app\lib\tasks;
use app\helpers\ArrayHelper;
use app\helpers\JsonHelper;
use app\lib\api\shop\gateways\CategoriesGateway;
use app\lib\api\shop\gateways\ProductsGateway;
use app\lib\api\shop\models\ExtProduct;
use app\lib\api\shop\query\ProductQuery;
use app\models\ExternalBrand;
use app\models\ExternalCategory;
use app\models\ExternalProduct;
use app\models\GeneratorSettings;
use app\models\Product;
use app\models\Shop;
use yii\base\Exception;

/**
 * Class UpdateProductsTask
 * @package app\lib\tasks
 */
class UpdateProductsTask extends AbstractTask
{
    const TASK_NAME = 'updateProducts';

    /**
     * @var ProductsGateway
     */
    protected $productsGateway;

    /**
     * @var array
     */
    protected $newProductIds = [];

    /**
     * @inheritDoc
     */
    protected function init()
    {
        parent::init();
        $this->logger = $this->getLogger();
        $this->productsGateway = ProductsGateway::factory($this->task->shop);
    }

    /**
     * @inheritDoc
     */
    public function execute($params = [])
    {
        if ($this->task->shop->external_strategy != Shop::EXTERNAL_STRATEGY_API) {
            throw new Exception('Обновление товаров необходимо делать только для магазинов, работающих через API');
        }
        $start = time();
        /** @var GeneratorSettings $settings */
        $settings = GeneratorSettings::find()
            ->andWhere(['shop_id' => $this->task->shop_id])
            ->one();

        $defaultBrandIds = !empty($settings->brands) ? explode(',', $settings->brands) : [];
        $taskContext = $this->task->getContext();
        $brandIds = ArrayHelper::getValue($taskContext, 'brandIds', $defaultBrandIds);

        if (empty($brandIds)) {
            $this->logger->log('Пустой список брендов');
            throw new Exception('Пустой список брендов');
        }
        $this->categoriesUpdate($this->task->shop);
        foreach ($brandIds as $brandId) {
            $limit = 500;
            $offset = 0;
            $query = new ProductQuery();
            $query->byBrandId($brandId);
            $query->onlyActive(false);
            $query->limit($limit);
            while ($products = $this->productsGateway->findByQuery($query)) {

                $this->saveProducts($products);

                $offset += $limit;
                $query->offset($offset);
            }
        }

        ExternalProduct::updateAll(
            ['is_available' => 0],
            ['AND',
                ['<=', 'updated_at', date('Y-m-d H:i:s', $start)],
                ['shop_id' => $this->task->shop_id]
            ]
        );
    }

    /**
     * @param array $products
     * @throws Exception
     */
    protected function saveProducts(array $products)
    {
        $ids = ArrayHelper::getColumn($products, 'id');
        /** @var ExternalProduct[] $existsProducts */
        $existsProducts = ExternalProduct::find()
            ->andWhere([
                'shop_id' => $this->task->shop_id,
                'outer_id' => $ids
            ])
            ->indexBy('outer_id')
            ->all();

        $this->logger->log('Получены товары с id: ' . json_encode($ids));

        foreach ($products as $product) {
            $extProduct = new ExtProduct($product);

            if (in_array($extProduct->id, $this->newProductIds)) {
                continue;
            }

            if (!array_key_exists($extProduct->id, $existsProducts)) {
                $this->newProductIds[] = $extProduct->id;
                $productModel = new ExternalProduct([
                    'outer_id' => (string)$extProduct->id,
                    'shop_id' => $this->task->shop_id,
                    'title' => $extProduct->title,
                    'brand_id' => $this->getBrand($extProduct)->id,
                    'category_id' => $this->getCategory($extProduct)->id,
                    'is_available' => $extProduct->isAvailable,
                    'url' => $extProduct->href,
                    'picture' => $extProduct->image,
                    'price' => round($extProduct->price)
                ]);
                $this->logger->log('Добавление товара: ' . JsonHelper::encodeModelPretty($productModel));
            } else {
                $productModel = $existsProducts[$extProduct->id];
                if ($extProduct->isAvailable) {
                    $productModel->price = round($extProduct->price);
                }
                $productModel->title = $extProduct->title;
                $productModel->is_available = $extProduct->isAvailable;
                $productModel->url = $extProduct->href;
                $productModel->picture = $extProduct->image;
                $productModel->brand_id = $this->getBrand($extProduct)->id;
                $productModel->category_id = $this->getCategory($extProduct)->id;
                $this->logger->log('Обновление товара: ' . JsonHelper::encodeModelPretty($productModel));
            }

            if (!$productModel->save()) {
                $errors = $productModel->getFirstErrors();
                throw new Exception(reset($errors));
            }
        }
    }

    /**
     * Возвращает сохраненный бренд для товара
     *
     * @param ExtProduct $extProduct
     * @return ExternalBrand
     * @throws Exception
     */
    protected function getBrand(ExtProduct $extProduct)
    {
        $brandModel = ExternalBrand::find()
            ->andWhere([
                'shop_id' => $this->task->shop_id,
                'outer_id' => $extProduct->getBrandId()
            ])->one();

        if (!$brandModel) {
            $brandModel = new ExternalBrand([
                'outer_id' => $extProduct->getBrandId(),
                'title' => $extProduct->getBrandTitle(),
                'shop_id' => $this->task->shop_id
            ]);
            if (!$brandModel->save()) {
                $errors = $brandModel->getFirstErrors();
                throw new Exception(reset($errors));
            }
            $this->logger->log('Добавление бренда: ' . JsonHelper::encodeModelPretty($brandModel));
        }

        return $brandModel;
    }

    /**
     * @param ExtProduct $extProduct
     * @return ExternalCategory
     * @throws Exception
     */
    protected function getCategory(ExtProduct $extProduct)
    {
        $categoryModel = ExternalCategory::find()
            ->andWhere([
                'shop_id' => $this->task->shop_id,
                'outer_id' => $extProduct->getCategoryId()
            ])->one();

        if (!$categoryModel) {
            $categoryModel = new ExternalCategory([
                'outer_id' => $extProduct->getCategoryId(),
                'shop_id' => $this->task->shop_id,
                'title' => $extProduct->getCategory()
            ]);

            if (!$categoryModel->save()) {
                $errors = $categoryModel->getFirstErrors();
                throw new Exception(reset($errors));
            }
            $this->logger->log('Добавление категории: ' . JsonHelper::encodeModelPretty($categoryModel));
        }

        return $categoryModel;
    }

    /**
     * @param Shop $shop
     */
    protected function categoriesUpdate(Shop $shop)
    {
        /** @var CategoriesGateway $categoriesGateway */
        $categoriesGateway = CategoriesGateway::factory($shop);
        $categories = $categoriesGateway->getList();

        foreach ($categories as $category) {
            $extCategory = ExternalCategory::find()
                ->andWhere([
                    'shop_id' => $shop->id,
                    'outer_id' => $category['id']
                ])->one();

            if (!$extCategory) {
                $extCategory = new ExternalCategory([
                    'shop_id' => $shop->id,
                    'outer_id' => $category['id']
                ]);
            }
            $extCategory->attributes = [
                'parent_id' => $category['parent_id'],
                'title' => $category['title']
            ];
            $extCategory->save();
        }
    }

    /**
     * @inheritDoc
     */
    protected function getLogFileName()
    {
        return 'update_products_' . $this->task->id;
    }
}
