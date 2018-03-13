<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 23.08.16
 * Time: 7:56
 */

namespace app\lib\import;
use app\models\FileImport;

/**
 * Interface ImportInterface
 * @package app\lib\import
 */
interface ImportInterface
{
    /**
     * @param FileImport $fileName
     * @return mixed
     */
    public function import(FileImport $fileName);
}
