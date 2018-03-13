<?php

namespace app\lib\api\yandex\direct\query;
use app\helpers\ArrayHelper;


/**
 * Class CheckResult
 * @package app\lib\api\yandex\direct\query
 */
class CheckResult
{
    const CAMPAIGNS = 'CampaignIds';
    const ADGROUPS = 'AdGroupIds';
    const ADS = 'AdIds';

    /**
     * @var array
     */
    protected $modified = [];

    /**
     * @var array
     */
    protected $notFound = [];

    /**
     * @var array
     */
    protected $unprocessed = [];

    /**
     * @var string
     */
    protected $timestamp;

    /**
     * CheckResult constructor.
     * @param array $result
     */
    public function __construct(array $result)
    {
        $this->modified = ArrayHelper::getValue($result, 'Modified', []);
        $this->notFound = ArrayHelper::getValue($result, 'NotFound', []);
        $this->unprocessed = ArrayHelper::getValue($result, 'Unprocessed', []);
        $this->timestamp = ArrayHelper::getValue($result, 'Timestamp');
    }

    /**
     * @param null|string $key
     * @return array|mixed
     */
    public function getModified($key = null)
    {
        if ($key) {
            return ArrayHelper::getValue($this->modified, $key, []);
        }

        return $this->modified;
    }

    /**
     * @param null|string $key
     * @return array|mixed
     */
    public function getNotFound($key = null)
    {
        if ($key) {
            return ArrayHelper::getValue($this->notFound, $key, []);
        }

        return $this->notFound;
    }

    /**
     * @param null|string $key
     * @return array|mixed
     */
    public function getUnprocessed($key = null)
    {
        if ($key) {
            return ArrayHelper::getValue($this->unprocessed, $key, []);
        }
        return $this->unprocessed;
    }

    /**
     * @return string
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }
}
