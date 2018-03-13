<?php

namespace app\lib\reports;

/**
 * Interface ReportInterface
 * @package app\lib\reports
 */
interface ReportInterface
{
    /**
     * @param $params
     * @return mixed
     */
    public function send($params = []);
}
