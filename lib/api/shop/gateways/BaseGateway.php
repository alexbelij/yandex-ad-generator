<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 25.08.16
 * Time: 9:42
 */

namespace app\lib\api\shop\gateways;

use app\lib\api\shop\ApiResult;
use app\lib\api\shop\dataSource\ApiDataSource;
use app\lib\api\shop\dataSource\DataSourceInterface;
use app\lib\api\shop\dataSource\InternalDataSource;
use app\lib\api\shop\exceptions\ApiGatewayException;
use app\models\ExternalProduct;
use app\lib\api\shop\query\QueryInterface;
use app\models\ExternalBrand;
use app\models\ExternalCategory;
use app\models\Shop;
use yii\base\Exception;
use yii\data\BaseDataProvider;
use yii\helpers\ArrayHelper;

/**
 * Class AbstractGateway
 * @package app\lib\api\shop\gateways
 */
abstract class BaseGateway
{
    /**
     * @var DataSourceInterface
     */
    protected $dataSource;

    /**
     * AbstractGateway constructor.
     * @param DataSourceInterface $dataSource
     */
    public function __construct(DataSourceInterface $dataSource)
    {
        $this->dataSource = $dataSource;
    }

    /**
     * @param DataSourceInterface $dataSource
     * @return BaseGateway
     */
    public function setDataSource(DataSourceInterface $dataSource)
    {
        $this->dataSource = $dataSource;
        return $this;
    }

    /**
     * @return DataSourceInterface
     */
    public function getDataSource()
    {
        return $this->dataSource;
    }

    /**
     * @param QueryInterface $query
     * @return mixed
     */
    public function findByQuery(QueryInterface $query)
    {
        return $this->dataSource->findByQuery($query);
    }

    /**
     * @param int|int[] $ids
     * @return array
     */
    public function findByIds($ids)
    {
        return $this->dataSource->findByIds($ids);
    }

    /**
     * Выполнение запроса
     *
     * @param array|QueryInterface $query
     * @return array
     */
    public function query($query)
    {
        return $this->dataSource->query($query);
    }

    /**
     * Возвращает общее количество элементов
     * @param array|QueryInterface $query
     * @return mixed
     * @throws ApiGatewayException
     */
    public function totalCount($query)
    {
        return $this->dataSource->totalCount($query);
    }

    /**
     * @param array $params
     * @return ApiResult
     * @throws ApiGatewayException
     */
    public function execute($params = [])
    {
        return $this->dataSource->execute($params);
    }

    /**
     * @param Shop $shop
     * @return BaseGateway
     * @throws Exception
     */
    public static function factory(Shop $shop)
    {
        $gatewayClassName = substr(get_called_class(), strrpos(get_called_class(), '\\') + 1);
        $gatewayClassName = strtolower(str_replace('Gateway', '', $gatewayClassName));
        if ($shop->isFileLoadStrategy()) {
            $modelClassMap = [
                'brands' => ExternalBrand::className(),
                'categories' => ExternalCategory::className(),
                'products' => ExternalProduct::className()
            ];
            $dataSource = new InternalDataSource(ArrayHelper::getValue($modelClassMap, $gatewayClassName), $shop);

        } elseif ($shop->external_strategy == Shop::EXTERNAL_STRATEGY_API) {
            $apiUrlMap = [
                'brands' => $shop->brand_api_url,
                'categories' => $shop->category_api_url,
                'products' => $shop->product_api_url
            ];
            $apiUrl = ArrayHelper::getValue($apiUrlMap, $gatewayClassName);
            $dataSource = new ApiDataSource($apiUrl, $shop);
        } else {
            throw new Exception('Неизвестная стратегия работы с магазином');
        }

        return new static($dataSource);
    }
}
