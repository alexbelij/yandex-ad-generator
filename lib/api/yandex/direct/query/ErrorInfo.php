<?php
/**
 * Project Golden Casino.
 */

namespace app\lib\api\yandex\direct\query;

use yii\helpers\ArrayHelper;

/**
 * Class Error
 * @package app\lib\api\yandex\direct\query
 * @author Denis Dyadyun <sysadm85@gmail.com>
 */
class ErrorInfo
{
    /**
     * @var array
     */
    protected $data = [];

    /**
     * Error constructor.
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * @return mixed
     */
    public function getCode()
    {
        return ArrayHelper::getValue($this->data, 'Code');
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return ArrayHelper::getValue($this->data, 'Message');
    }

    /**
     * @return mixed
     */
    public function getDetails()
    {
        return ArrayHelper::getValue($this->data, 'Details');
    }

    /**
     * Выводит подробную информацию об ошибке
     *
     * @return string
     */
    public function errorInfo()
    {
        return $this->getCode() . ': ' . $this->getMessage() . ', details: ' . $this->getDetails();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->errorInfo();
    }
}
