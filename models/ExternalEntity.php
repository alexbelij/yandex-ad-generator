<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 26.08.16
 * Time: 9:33
 */

namespace app\models;

/**
 * Class ExternalEntity
 * @package app\models
 */
class ExternalEntity extends BaseModel
{
    /**
     * @var int
     */
    protected $fileImportId;

    /**
     * @return int
     */
    public function getFileImportId()
    {
        return $this->fileImportId;
    }

    /**
     * @param int $fileImportId
     * @return ExternalEntity
     */
    public function setFileImportId($fileImportId)
    {
        $this->fileImportId = $fileImportId;
        return $this;
    }
}
