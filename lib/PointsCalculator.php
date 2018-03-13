<?php
/**
 * Project Golden Casino.
 */

namespace app\lib;

/**
 * Класс занимается подсчетам потраченных баллов по операциям
 *
 * Class PointsCalculator
 * @package app\lib
 * @author Denis Dyadyun <sysadm85@gmail.com>
 */
class PointsCalculator
{
    const ADS = 'ads';
    const ADGROUPS = 'adgroups';
    const CAMPAIGNS = 'campaigns';
    const KEYWORDS = 'keywords';
    const SITELINKS = 'sitelinks';
    const VCARDS = 'vcards';
    const CHECKS = 'checks';
    const BIDS = 'bids';

    const OP_ADD = 'add';
    const OP_UPDATE = 'update';
    const OP_ARCHIVE = 'archive';
    const OP_UNARCHIVE = 'unarchive';
    const OP_GET = 'get';
    const OP_SUSPEND = 'suspend';
    const OP_RESUME = 'resume';
    const OP_MODERATE = 'moderate';
    const OP_DELETE = 'delete';
    const OP_CHECK = 'check';
    const OP_SET = 'set';

    /**
     * @var PointsCalculator
     */
    private static $instance;

    /**
     * @var array
     */
    private static $pointsMap;

    /**
     * @var array
     */
    private $points = [];

    /**
     * @var int
     */
    private $lastPointsCount = 0;

    /**
     * @return PointsCalculator
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @return array|mixed
     */
    public function getPointsMap()
    {
        if (is_null(self::$pointsMap)) {
            self::$pointsMap = require_once \Yii::getAlias('@app/config/yandex/points.php');
        }

        return self::$pointsMap;
    }

    /**
     * @param string $type
     * @param string $op
     * @param int $unitCount
     * @return int
     */
    public function inc($type, $op, $unitCount)
    {
        $pointsMap = $this->getPointsMap();
        $perRequestCost = $pointsMap[$type][$op]['per_request'];
        $perUnitCost = $pointsMap[$type][$op]['per_unit'];

        if (!isset($this->points[$type])) {
            $this->points[$type] = 0;
        }

        $cost = $perRequestCost + $perUnitCost * $unitCount;
        $this->lastPointsCount += $cost;
        $this->points[$type] += $cost;

        return $cost;
    }

    /**
     * @return int
     */
    public function getLastPointsAndClean()
    {
        $points = $this->lastPointsCount;
        $this->lastPointsCount = 0;

        return $points;
    }

    /**
     * @return number
     */
    public function getTotal()
    {
        return array_sum($this->points);
    }

    /**
     * @return array
     */
    public function getPoints()
    {
        return $this->points;
    }

    /**
     * Обнулить статистику по баллам
     */
    public function reset()
    {
        $this->points = [];
    }
}
