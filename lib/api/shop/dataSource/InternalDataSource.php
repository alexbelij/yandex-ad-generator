<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 25.08.16
 * Time: 9:41
 */

namespace app\lib\api\shop\dataSource;

use app\lib\api\shop\mapper\BrandMapper;
use app\lib\api\shop\mapper\CategoryMapper;
use app\lib\api\shop\mapper\MapperFactory;
use app\lib\api\shop\mapper\ProductMapper;
use app\lib\api\shop\mapper\ModelMapperInterface;
use app\lib\api\shop\query\QueryInterface;
use app\lib\api\shop\query\translator\TranslatorFactory;
use app\models\Shop;
use yii\base\Exception;
use yii\db\ActiveQuery;

/**
 * Class ExternalProductDataSource
 * @package app\lib\api\shop\dataSource
 */
class InternalDataSource implements DataSourceInterface
{
    /**
     * @var string
     */
    protected $modelClass;

    /**
     * @var ModelMapperInterface
     */
    protected $mapper;

    /**
     * @var Shop
     */
    protected $shop;

    /**
     * InternalDataSource constructor.
     * @param $modelClass
     * @param Shop $shop
     */
    public function __construct($modelClass, Shop $shop)
    {
        $this->modelClass = $modelClass;
        $this->shop = $shop;
    }

    /**
     * @return Shop
     */
    public function getShop()
    {
        return $this->shop;
    }

    /**
     * @inheritDoc
     */
    public function findByQuery(QueryInterface $query)
    {
        return $this->query($query);
    }

    /**
     * @inheritDoc
     */
    public function findByIds($ids)
    {
        return $this->createResult($this->getActiveQuery(['ids' => $ids])->all());
    }

    /**
     * @inheritDoc
     */
    public function query($criteria)
    {
        return $this->createResult($this->getActiveQuery($criteria)->all());
    }

    /**
     * @inheritDoc
     */
    public function totalCount($query)
    {
        $count = $this->getActiveQuery($query)->count();
        return $count;
    }

    /**
     * @return string
     */
    public function getModelClass()
    {
        return $this->modelClass;
    }

    /**
     * @return ModelMapperInterface
     * @throws Exception
     */
    protected function getMapper()
    {
        if (is_null($this->mapper)) {
            $this->mapper = MapperFactory::create($this->modelClass, $this->shop);
        }

        return $this->mapper;
    }

    /**
     * @param mixed $query
     * @return ActiveQuery
     */
    protected function getActiveQuery($query)
    {
        if ($query instanceof QueryInterface) {
            $query = $query->getQuery($this);
        } else {
            $query = TranslatorFactory::create($this)->translate($query);
        }

        return $query;
    }

    /**
     * @param array $items
     * @return array
     */
    protected function createResult(array $items)
    {
        return $this->getMapper()->createResult($items);
    }
}
