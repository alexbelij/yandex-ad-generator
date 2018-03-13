<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 22.03.16
 * Time: 21:19
 */

namespace app\lib\api\yandex\direct\exceptions;

use yii\base\Exception;

/**
 * Class YandexException
 * @package app\lib\api\yandex\direct\exceptions
 */
class YandexException extends Exception
{
    /**
     * @var string
     */
    protected $details;

    /**
     * @return string
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * @param string $details
     * @return $this
     */
    public function setDetails($details)
    {
        $this->details = $details;
        return $this;
    }
}
