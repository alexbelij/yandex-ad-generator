<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 30.10.16
 * Time: 8:12
 */

namespace app\lib\data;

use yii\data\Pagination;

/**
 * Class ManualPagination
 * @package app\lib\data
 */
class ManualPagination extends Pagination
{
    /**
     * @var int
     */
    public $limit;

    /**
     * @var int
     */
    public $offset;
}
