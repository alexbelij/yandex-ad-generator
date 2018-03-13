<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 09.03.16
 * Time: 21:02
 */

namespace app\lib\api\shop\dataSource;

use app\lib\api\shop\exceptions\ApiGatewayException;
use app\lib\api\shop\ApiResult;
use app\lib\api\shop\query\QueryInterface;
use app\models\Shop;
use Zend\Http\Client;

/**
 * Class AbstractGateway
 * @package app\lib\productsSource\gateways
 */
class ApiDataSource implements DataSourceInterface
{
    /**
     * Адрес API ресурса
     * @var string
     */
    protected $apiUrl;

    /**
     * @var Client
     */
    protected $httpClient;

    /**
     * @var string
     */
    protected $apiKey;

    /**
     * @var Shop
     */
    protected $shop;

    /**
     * Количество данных, полученных за один раз от апи
     * @var int
     */
    protected $itemsLimit = 200;

    /**
     * ApiDataSource constructor.
     * @param $apiUrl
     * @param Shop $shop
     */
    public function __construct($apiUrl, Shop $shop)
    {
        $this->apiUrl = $apiUrl;
        $this->shop = $shop;
        $this->apiKey = $shop->api_secret_key;
    }

    /**
     * @param QueryInterface $search
     * @return array
     * @throws ApiGatewayException
     */
    public function findByQuery(QueryInterface $search)
    {
        return $this->query($search->getQuery($this));
    }

    /**
     * @param int|int[] $ids
     * @return array
     */
    public function findByIds($ids)
    {
        return $this->query(['ids' => implode(',', (array)$ids)]);
    }

    /**
     * Выполнение запроса
     *
     * @param array|QueryInterface $query
     * @return array
     */
    public function query($query)
    {
        if ($query instanceof QueryInterface) {
            $query = $query->getQuery($this);
        }
        $query['page'] = !empty($query['page']) ? $query['page'] : 1;
        $limit = !empty($query['limit']) ? $query['limit'] : null;

        $items = [];
        $allCount = 0;

        while (true) {

            $response = $this->execute($query);
            $allCount += $response->getCount();

            if ($limit && $allCount > $limit) {
                $leftCount = $limit - count($items);
                $items = array_merge($items, array_slice($response->getItems(), 0, $leftCount));
            } else {
                $items = array_merge($items, $response->getItems());
            }
            $query['page'] += 1;

            if ($response->isAllDataTransferred()
                || !$response->getCount()
                || ($limit && $allCount >= $limit)
            ) {
                break;
            }
        }

        return $items;
    }

    /**
     * Возвращает общее количество элементов
     * @param array|QueryInterface $query
     * @return mixed
     * @throws ApiGatewayException
     */
    public function totalCount($query)
    {
        if ($query instanceof QueryInterface) {
            $params = $query->getQuery($this);
        } else {
            $params = $query;
        }
        $params['limit'] = 1;
        $params['page'] = 1;

        return $this->execute($params)->getTotalCount();
    }

    /**
     * @param array $params
     * @return ApiResult
     * @throws ApiGatewayException
     */
    public function execute($params = [])
    {
        $params = array_merge(
            (array)$params, $this->getRequestServiceData()
        );

        if (empty($params['limit'])) {
            $params['limit'] = $this->itemsLimit;
        }

        $query = http_build_query($params);

        $url = $this->apiUrl;
        if ($query) {
            $url .= '?' . $query;
        }

        $response = $this->getHttpClient()
            ->reset()
            ->setUri($url)
            ->send();

        if ($response->getStatusCode() !== 200) {
            throw new ApiGatewayException('Response status is' . $response->getStatusCode());
        }

        if ($response->getBody() == '') {
            throw new ApiGatewayException('Empty response');
        }

        $result = json_decode($response->getBody(), true);

        if (!is_array($result)) {
            throw new ApiGatewayException(json_last_error_msg());
        }

        return new ApiResult($result);
    }

    /**
     * Служебные данные для запроса
     * @return array
     */
    protected function getRequestServiceData()
    {
        return [
            'apiKey' => $this->getApiKey()
        ];
    }

    /**
     * @return Client
     */
    protected function getHttpClient()
    {
        if (is_null($this->httpClient)) {
            $this->httpClient = new Client();
        }

        return $this->httpClient;
    }

    /**
     * @return mixed
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * @param $apiKey
     * @return $this
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getShop()
    {
        return $this->shop;
    }
}
