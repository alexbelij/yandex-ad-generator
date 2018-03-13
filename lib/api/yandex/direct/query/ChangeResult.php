<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 27.03.16
 * Time: 19:03
 */

namespace app\lib\api\yandex\direct\query;

/**
 * Class ChangeResult
 * @package app\lib\api\yandex\direct\query
 * @author Denis Dyadyun <sysadm85@gmail.com>
 */
class ChangeResult implements \Iterator
{
    /**
     * @var ResultItem[]
     */
    protected $result = [];

    /**
     * ModifyResult constructor.
     * @param array $result
     */
    public function __construct(array $result = [])
    {
        foreach ($result as $item) {
            $this->result[] = new ResultItem($item);
        }
    }

    /**
     * @param ChangeResult $result
     * @return $this
     */
    public function merge(ChangeResult $result)
    {
        foreach ($result->getResult() as $item) {
            $this->result[] = $item;
        }

        return $this;
    }

    /**
     * @return ErrorInfo|null
     */
    public function firstError()
    {
        $errors = $this->getErrors();

        return reset($errors);
    }

    /**
     * Возвращает список id по которым происходили операции
     * @return array
     */
    public function getIds()
    {
        $ids = [];
        foreach ($this->result as $resultItem) {
            $ids[] = $resultItem->getId();
        }

        return array_filter($ids);
    }

    /**
     * Возвращает массив ошибок
     * @return ErrorInfo[]
     */
    public function getErrors()
    {
        $errors = [];
        foreach ($this->result as $itemResult) {
            if ($itemResult->hasError()) {
                foreach ($itemResult->getErrors() as $errorInfo) {
                    $errors[] = $errorInfo;
                }
            }
        }

        return $errors;
    }

    /**
     * Все операции прошли успешно?
     * @return bool
     */
    public function isSuccess()
    {
        return count($this->getErrors()) == 0;
    }

    /**
     * @inheritDoc
     */
    public function current()
    {
        return current($this->result);
    }

    /**
     * @inheritDoc
     */
    public function next()
    {
        next($this->result);
    }

    /**
     * @inheritDoc
     */
    public function key()
    {
        return key($this->result);
    }

    /**
     * @inheritDoc
     */
    public function valid()
    {
        return key($this->result) !== null;
    }

    /**
     * @inheritDoc
     */
    public function rewind()
    {
        reset($this->result);
    }

    /**
     * @return ResultItem[]
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->result);
    }

    /**
     * @param string $message
     * @return static
     */
    public static function createErrorWithMessage($message)
    {
        return new static(
            [
                [
                    'Errors' => [
                        [
                            'Message' => $message
                        ]
                    ]
                ]
            ]
        );
    }
}
