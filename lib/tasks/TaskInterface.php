<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 09.04.16
 * Time: 14:24
 */

namespace app\lib\tasks;

interface TaskInterface
{
    /**
     * Запуск операции
     *
     * @param array $params
     * @return mixed
     */
    public function execute($params = []);
}
