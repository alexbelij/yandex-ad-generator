<?php

namespace app\lib\provider;

use app\lib\api\shop\gateways\ApiDataSource;
use app\lib\api\shop\gateways\BaseGateway;
use app\lib\api\shop\query\BaseQuery;
use app\lib\provider\processStrategies\AfterLoadProcessStrategy;
use yii\base\InvalidParamException;
use yii\data\BaseDataProvider;

/**
 * Провайдер для работы с апи магазинов
 *
 * Class ApiDataProvider
 * @package app\lib\provider
 * @author Denis Dyadyun <sysadm85@gmail.com>
 */
class ApiDataProvider extends BaseDataProvider
{
    /**
     * @var BaseGateway
     */
    public $gateway;

    /**
     * @var BaseQuery
     */
    public $query;

    /**
     * @var string|callable
     */
    public $key;

    /**
     * @var AfterLoadProcessStrategy
     */
    public $processStrategy;

    /**
     * @var
     */
    protected $totalCount;

    /**
     * @inheritDoc
     */
    public function init()
    {
        parent::init();
        if (!$this->gateway) {
            throw new InvalidParamException('Gateway is required');
        }
        if (!$this->query) {
            $this->query = new BaseQuery();
        }
    }

    /**
     * @inheritDoc
     */
    protected function prepareModels()
    {
        $pagination = $this->getPagination();

        if ($pagination === false) {
            return $this->applyProcessStrategy(
                $this->gateway->findByQuery($this->query)
            );
        } else {
            $pagination->totalCount = $this->prepareTotalCount();
            $query = clone $this->query;
            $query
                ->limit($pagination->getLimit())
                ->setPage($pagination->getPage()+1);

            return $this->applyProcessStrategy(
                $this->gateway->findByQuery($query)
            );
        }
    }

    /**
     * @param array $models
     * @return mixed
     */
    protected function applyProcessStrategy($models)
    {
        if ($this->processStrategy && is_callable($this->processStrategy)) {
            $callback = $this->processStrategy;
            return call_user_func($callback, $models);
        } elseif ($this->processStrategy instanceof AfterLoadProcessStrategy) {
            return $this->processStrategy->process($models);
        }

        return $models;
    }

    /**
     * @inheritDoc
     */
    protected function prepareKeys($models)
    {
        if ($this->key !== null) {
            $keys = [];
            foreach ($models as $model) {
                if (is_string($this->key)) {
                    $keys[] = $model[$this->key];
                } else {
                    $keys[] = call_user_func($this->key, $model);
                }
            }

            return $keys;
        } else {
            return array_keys($models);
        }
    }

    /**
     * @inheritDoc
     */
    protected function prepareTotalCount()
    {
        $query = clone $this->query;
        if (is_null($this->totalCount)) {
            $this->totalCount = $this->gateway->totalCount($query);
        }

        return $this->totalCount;
    }
}
