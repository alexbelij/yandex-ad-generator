<?php

namespace app\lib\api\yandex\direct\resources;

use app\lib\api\yandex\direct\query\ChangeResult;
use app\lib\PointsCalculator;

/**
 * Class AdResource
 * @package app\lib\api\yandex\direct\resources
 */
class AdResource extends AbstractResource
{
    public $resourceName = 'Ads';
    
    public $queryClass = 'app\lib\api\yandex\direct\query\AdQuery';

    /**
     * Остановка показа объявлений
     *
     * @param int|int[] $ids
     * @return ChangeResult
     */
    public function suspend($ids)
    {
        $ids = (array) $ids;

        $result = $this->query(['SelectionCriteria' => ['Ids' => array_values($ids)]], 'suspend');
        $this->getPointsCalculator()->inc($this->getType(), PointsCalculator::OP_SUSPEND, count($ids));

        return new ChangeResult($result['result']['SuspendResults']);
    }

    /**
     * Отправка объявления на модерацию
     *
     * @param int $ids
     * @return ChangeResult
     */
    public function moderate($ids)
    {
        $result = $this->query(['SelectionCriteria' => ['Ids' => array_values((array) $ids)]], 'moderate');
        $this->getPointsCalculator()->inc($this->getType(), PointsCalculator::OP_MODERATE, count($ids));

        return new ChangeResult($result['result']['ModerateResults']);
    }

    /**
     * Возобновить показы
     *
     * @param int|int[] $ids
     * @return ChangeResult
     */
    public function resume($ids)
    {
        $result = $this->query(['SelectionCriteria' => ['Ids' => array_values((array) $ids)]], 'resume');
        $this->getPointsCalculator()->inc($this->getType(), PointsCalculator::OP_RESUME, count($ids));

        return new ChangeResult($result['result']['ResumeResults']);
    }

    /**
     * Убрать объвление с показа
     *
     * @param int|int[] $ids
     * @return bool
     */
    public function removeAd($ids)
    {
        $res = $this->suspend($ids);

        return $res->isSuccess();
    }

    /**
     * Архивация записи
     *
     * @param int|int[] $ids
     * @return ChangeResult
     */
    public function unarchive($ids)
    {
        $ids = (array)$ids;

        $result = $this->query(['SelectionCriteria' => ['Ids' => $ids]], 'unarchive');
        $this->getPointsCalculator()->inc($this->getType(), PointsCalculator::OP_ARCHIVE, count($ids));

        return new ChangeResult($result['result']['UnarchiveResults']);
    }
}
