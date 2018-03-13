<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 12.11.16
 * Time: 17:36
 */

namespace app\lib\import\yml\strategies\mappers;
use app\helpers\ArrayHelper;

/**
 * Class AbstractMapper
 * @package app\lib\import\yml\strategies\mappers
 */
abstract class AbstractMapper
{
    /**
     * @var array
     */
    protected $data = [];

    /**
     * AbstractMapper constructor.
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * @inheritDoc
     */
    public function __get($name)
    {
        return ArrayHelper::getValue($this->data, $name);
    }

    /**
     * @inheritDoc
     */
    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

    /**
     * @param mixed $data
     * @return mixed
     */
    abstract public function map($data = []);
}
