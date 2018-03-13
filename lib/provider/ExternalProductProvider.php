<?php

namespace app\lib\provider;

use app\lib\provider\processStrategies\AfterLoadProcessStrategy;
use app\models\Shop;
use yii\base\InvalidConfigException;
use yii\base\InvalidParamException;
use yii\data\BaseDataProvider;
use yii\db\ActiveQuery;
use yii\db\QueryInterface;

/**
 * Class ExternalProductProvider
 * @package app\lib\provider
 */
class ExternalProductProvider extends BaseDataProvider
{
    /**
     * @var string|callable
     */
    public $key;

    /**
     * @var AfterLoadProcessStrategy
     */
    public $processStrategy;

    /**
     * @var int
     */
    protected $totalCount;

    /**
     * @var ActiveQuery
     */
    public $query;

    /**
     * @var Shop
     */
    public $shop;

    /**
     * @inheritDoc
     */
    public function init()
    {
        parent::init();
        if (!$this->shop) {
            throw new InvalidParamException('Shop is required');
        }

        if (!$this->query) {
            throw new InvalidParamException('Query is required');
        }
    }

    /**
     * @inheritDoc
     */
    protected function prepareModels()
    {
        $query = clone $this->query;

        if (($pagination = $this->getPagination()) !== false) {
            $pagination->totalCount = $this->getTotalCount();
            $query->limit($pagination->getLimit())->offset($pagination->getOffset());
        }

        return $this->applyProcessStrategy($query->all());
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
        if (!$this->query instanceof QueryInterface) {
            throw new InvalidConfigException('The "query" property must be an instance of a class that implements the QueryInterface e.g. yii\db\Query or its subclasses.');
        }
        $query = clone $this->query;

        return (int) $query->limit(-1)->offset(-1)->orderBy([])->count('*');
    }
}