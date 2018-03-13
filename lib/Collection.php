<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 08.10.16
 * Time: 8:31
 */

namespace app\lib;

/**
 * Class Collection
 * @package app\lib
 */
class Collection implements \Iterator
{
    /**
     * @var array
     */
    protected $items = [];

    /**
     * @var int
     */
    protected $ind = 0;

    /**
     * Collection constructor.
     * @param array $items
     */
    public function __construct(array $items)
    {
        $this->items = $items;
    }

    /**
     * @return array
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param array $items
     * @return Collection
     */
    public function setItems($items)
    {
        $this->items = $items;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function current()
    {
        return $this->items[$this->ind];
    }

    /**
     * @inheritDoc
     */
    public function next()
    {
        $this->ind++;
    }

    /**
     * @inheritDoc
     */
    public function key()
    {
        return $this->ind;
    }

    /**
     * @inheritDoc
     */
    public function valid()
    {
        return $this->ind < count($this->items);
    }

    /**
     * @inheritDoc
     */
    public function rewind()
    {
        $this->ind = 0;
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->items);
    }

    /**
     * @param int $count
     * @return BatchIterator
     */
    public function batch($count = 10)
    {
        return new BatchIterator($this->items, $count);
    }
}
