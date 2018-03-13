<?php

namespace app\lib\services;

use app\components\LoggerInterface;
use app\helpers\ArrayHelper;
use app\lib\api\shop\models\ExtProduct;
use app\models\Ad;
use yii\base\Object;

/**
 * Класс проверяет нужно ли сохрянять ключевые фразы для объявления
 *
 * Class KeywordsUpdateChecker
 * @package app\lib\services
 */
class KeywordsUpdateChecker extends Object
{
    /**
     * @var string
     */
    public $dateFrom;

    /**
     * @var string
     */
    public $dateTo;

    /**
     * @var boolean
     */
    public $onlyActive;

    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $adTitle;

    /**
     * @var boolean
     */
    public $isRequireVerification;

    /**
     * @var bool
     */
    public $withoutAd;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param ExtProduct $product
     * @param Ad $ad
     * @return bool
     */
    public function isNeedUpdate(ExtProduct $product, Ad $ad)
    {
        if (!$this->dateTo &&
            !$this->dateFrom &&
            !$this->title &&
            !$this->adTitle &&
            is_null($this->isRequireVerification) &&
            is_null($this->withoutAd)
        ) {
            return true;
        }

        if ($this->dateFrom && strtotime($product->createdAt) < strtotime($this->dateFrom)) {
            $this->log('dateFrom');
            return false;
        }

        if ($this->dateTo && strtotime($product->createdAt) > strtotime($this->dateTo)) {
            $this->log('dateTo');
            return false;
        }

        if ($this->title && !preg_match("#{$this->title}#ui", $product->title)) {
            $this->log("#{$this->title}#ui и $product->title");
            $this->log('title');
            return false;
        }

        if ($this->adTitle && !preg_match("#{$this->adTitle}#ui", $ad->title)) {
            $this->log("#{$this->adTitle}#ui, $ad->title");
            $this->log('adTitle');
            return false;
        }

        if (!is_null($this->isRequireVerification) && !$ad->isNewRecord && $ad->is_require_verification != $this->isRequireVerification) {
            $this->log('isRequireVerification');
            return false;
        }

        return true;
    }

    /**
     * @param array $context
     * @return KeywordsUpdateChecker
     */
    public static function createFromContext(array $context)
    {
        return new self([
            'dateFrom' => ArrayHelper::getValue($context, 'dateFrom'),
            'dateTo' => ArrayHelper::getValue($context, 'dateTo'),
            'onlyActive' => ArrayHelper::getValue($context, 'onlyActive', false),
            'title' => ArrayHelper::getValue($context, 'title'),
            'adTitle' => ArrayHelper::getValue($context, 'adTitle'),
            'isRequireVerification' => ArrayHelper::getValue($context, 'isRequireVerification'),
            'withoutAd' => ArrayHelper::getValue($context, 'withoutAd'),
        ]);
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param $msg
     */
    protected function log($msg)
    {
        if ($this->logger) {
            $this->logger->log($msg);
        }
    }
}
