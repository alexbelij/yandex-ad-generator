<?php

namespace app\lib\api\yandex\direct\resources;

use app\lib\api\yandex\direct\Connection;
use app\lib\api\yandex\direct\query\AbstractQuery;
use app\lib\api\yandex\direct\query\ChangeResult;
use app\lib\api\yandex\direct\query\Result;
use app\lib\PointsCalculator;
use yii\helpers\ArrayHelper;

/**
 * Class AbstractResource
 * @package app\lib\api\yandex\direct\resources
 * @author Denis Dyadyun <sysadm85@gmail.com>
 */
abstract class AbstractResource
{
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * Название ресурса получения данных
     * @var string
     */
    protected $resourceName = '';

    /**
     * @var string
     */
    protected $baseStructure = '';

    /**
     * @var string
     */
    protected $queryClass;

    /**
     * @var PointsCalculator
     */
    protected $pointsCalculator;

    /**
     * AbstractResource constructor.
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @return PointsCalculator
     */
    public function getPointsCalculator()
    {
        if (is_null($this->pointsCalculator)) {
            $this->pointsCalculator = PointsCalculator::getInstance();
        }

        return $this->pointsCalculator;
    }

    /**
     * @param PointsCalculator $pointsCalculator
     */
    public function setPointsCalculator($pointsCalculator)
    {
        $this->pointsCalculator = $pointsCalculator;
    }

    /**
     * Поиск сущности по id
     *
     * @param mixed $id
     * @param array $fieldNames
     * @return Result
     */
    public function findByIds($id, $fieldNames = [])
    {
        $query = new $this->queryClass(['ids' => $id]);
        return $this->find($query, $fieldNames);
    }

    /**
     * Поиск
     *
     * @param AbstractQuery|array $query
     * @param array $fieldNames
     * @return Result
     * @throws \app\lib\api\yandex\direct\exceptions\ConnectionException
     */
    public function find($query, $fieldNames = [])
    {
        if (!$query instanceof AbstractQuery) {
            $query = new $this->queryClass($query);
        }

        if (!empty($fieldNames)) {
            $query->setFieldNames($fieldNames);
        }

        $result = $this->connection->query($this->resourceName, $query->getQuery());
        $this->getPointsCalculator()->inc($this->getType(), PointsCalculator::OP_GET, $this->getResultCount($result));

        return $this->createResult($result);
    }

    /**
     * Добавление новых ресурсов
     * @param array|array[] $params
     * @return ChangeResult
     * @throws \app\lib\api\yandex\direct\exceptions\ConnectionException
     */
    public function add($params)
    {
        $resourceName = ucfirst($this->getBaseStructure());
        if (ArrayHelper::isAssociative($params)) {
            $params = [$params];
        }

        $data = [$resourceName => $params];

        $result = $this->connection->query($this->resourceName, $data, 'add');

        $this->getPointsCalculator()->inc($this->getType(), PointsCalculator::OP_ADD, count($params));

        return new ChangeResult($result['result']['AddResults']);
    }

    /**
     * Обновление записи/записей
     *
     * @param $item
     * @return ChangeResult
     * @throws \app\lib\api\yandex\direct\exceptions\ConnectionException
     */
    public function update($item)
    {
        $resourceName = ucfirst($this->getBaseStructure());

        $items = ArrayHelper::isAssociative($item) ? [$item] : $item;

        $result = $this->connection->query($this->resourceName, [$resourceName => $items], 'update');
        $this->getPointsCalculator()->inc($this->getType(), PointsCalculator::OP_UPDATE, count($items));

        return new ChangeResult($result['result']['UpdateResults']);
    }

    /**
     * Архивация записи
     *
     * @param int|int[] $ids
     * @return ChangeResult
     */
    public function archive($ids)
    {
        $ids = (array)$ids;

        $result = $this->query(['SelectionCriteria' => ['Ids' => $ids]], 'archive');
        $this->getPointsCalculator()->inc($this->getType(), PointsCalculator::OP_ARCHIVE, count($ids));

        return new ChangeResult($result['result']['ArchiveResults']);
    }

    /**
     * Удаление записи/записей
     *
     * @param int|int[] $ids
     * @return ChangeResult
     * @throws \app\lib\api\yandex\direct\exceptions\ConnectionException
     */
    public function delete($ids)
    {
        $ids = array_values((array)$ids);

        $result = $this->connection->query($this->resourceName, ['SelectionCriteria' => ['Ids' => $ids]], 'delete');
        $this->getPointsCalculator()->inc($this->getType(), PointsCalculator::OP_DELETE, count($ids));

        return new ChangeResult($result['result']['DeleteResults']);
    }

    /**
     * Выполнение запроса к api
     *
     * @param array $params
     * @param string $method
     * @return mixed
     * @throws \app\lib\api\yandex\direct\exceptions\ConnectionException
     */
    protected function query(array $params = [], $method = 'get')
    {
        return $this->connection->query($this->resourceName, $params, $method);
    }

    /**
     * @param array $result
     * @return Result|array
     */
    protected function createResult($result)
    {
        $resultField = ucfirst($this->getBaseStructure());

        if (empty($result['result'][$resultField])) {
            return new Result();
        }

        $result = $result['result'];

        $meta = [];
        if (isset($result['LimitedBy'])) {
            $meta['limitedBy'] = $result['LimitedBy'];
        }

        return new Result($result[$resultField], $meta);
    }

    /**
     * @return string
     */
    protected function getBaseStructure()
    {
        return !empty($this->baseStructure) ? $this->baseStructure : $this->resourceName;
    }

    /**
     * @return string
     */
    protected function getType()
    {
        return strtolower($this->resourceName);
    }

    /**
     * @return Connection
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * @param Connection $connection
     * @return AbstractResource
     */
    public function setConnection($connection)
    {
        $this->connection = $connection;
        return $this;
    }

    /**
     * @param array $result
     * @return int
     */
    protected function getResultCount($result)
    {
        $resultField = ucfirst($this->getBaseStructure());

        $count = 1;
        if (!empty($result[$resultField])) {
            $count = count($result[$resultField]);
        }

        return $count;
    }
}
