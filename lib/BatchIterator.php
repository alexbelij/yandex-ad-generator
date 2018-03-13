<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 21.10.16
 * Time: 20:26
 */

namespace app\lib;

/**
 * Class BatchIterator
 * @package app\lib
 */
class BatchIterator implements \Iterator
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
     * @var int
     */
    protected $length = 1;

    /**
     * @var int
     */
    protected $iterationKey = 0;

    /**
     * BatchIterator constructor.
     * @param array $items
     * @param int $length
     */
    public function __construct(array $items, $length)
    {
        $this->items = $items;
        $this->length = $length;
    }

    /**
     * @inheritDoc
     */
    public function current()
    {
        return array_slice($this->items, $this->ind, $this->length);
    }

    /**
     * @inheritDoc
     */
    public function next()
    {
        $this->ind += $this->length;
        $this->iterationKey++;
    }

    /**
     * @inheritDoc
     */
    public function key()
    {
        return $this->iterationKey;
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
        $this->iterationKey = 0;
    }
}
