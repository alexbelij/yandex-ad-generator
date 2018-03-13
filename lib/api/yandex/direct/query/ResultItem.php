<?php
/**
 * Project Golden Casino.
 */

namespace app\lib\api\yandex\direct\query;

use yii\helpers\ArrayHelper;

/**
 * Class ResultItem
 * @package app\lib\api\yandex\direct\query
 * @author Denis Dyadyun <sysadm85@gmail.com>
 */
class ResultItem
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var array
     */
    protected $warnings = [];

    /**
     * @var array
     */
    protected $errors = [];

    /**
     * ResultItem constructor.
     * @param array $resultData
     */
    public function __construct(array $resultData)
    {
        $this->id = ArrayHelper::getValue($resultData, 'Id');
        $warnings = ArrayHelper::getValue($resultData, 'Warnings', []);
        $errors = ArrayHelper::getValue($resultData, 'Errors', []);

        foreach ($warnings as $warningInfo) {
            $this->warnings[] = new ErrorInfo($warningInfo);
        }

        foreach ($errors as $errorInfo) {
            $this->errors[] = new ErrorInfo($errorInfo);
        }
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return ResultItem
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return ErrorInfo[]
     */
    public function getWarnings()
    {
        return $this->warnings;
    }

    /**
     * @param array $warnings
     * @return ResultItem
     */
    public function setWarnings($warnings)
    {
        $this->warnings = $warnings;
        return $this;
    }

    /**
     * @return ErrorInfo[]
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @param array $errors
     * @return ResultItem
     */
    public function setErrors($errors)
    {
        $this->errors = $errors;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasError()
    {
        return !empty($this->errors);
    }

    /**
     * @return bool
     */
    public function isOk()
    {
        return empty($this->errors) && empty($this->warnings);
    }

    /**
     * @return ErrorInfo
     */
    public function firstError()
    {
        return reset($this->errors);
    }

    /**
     * @return ErrorInfo
     */
    public function firstWarning()
    {
        return reset($this->warnings);
    }
}
