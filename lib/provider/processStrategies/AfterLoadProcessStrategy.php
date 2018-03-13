<?php

namespace app\lib\provider\processStrategies;

/**
 * Интрфейс обработки данных после загрузки через апи
 *
 * Interface LoadStrategyInterface
 * @package app\lib\provider\loadStrategies
 */
interface AfterLoadProcessStrategy
{
    /**
     * @param array $models
     * @return mixed
     */
    public function process($models);
}
