<?php

namespace app\lib;

/**
 * Class UploadedFile
 * @package app\lib
 */
class UploadedFile extends \yii\web\UploadedFile
{
    /**
     * @var array
     */
    private static $errorMessages = [
        UPLOAD_ERR_INI_SIZE => 'Размер принятого файла превысил максимально допустимый размер',
        UPLOAD_ERR_FORM_SIZE => 'Размер загружаемого файла превысил значение MAX_FILE_SIZE, указанное в HTML-форме',
        UPLOAD_ERR_PARTIAL => 'Загружаемый файл был получен только частично',
        UPLOAD_ERR_NO_FILE => 'Файл не был получен',
        UPLOAD_ERR_NO_TMP_DIR => 'Отсутствует временная папка',
        UPLOAD_ERR_CANT_WRITE => 'Не удалось записать файл на диск',
        UPLOAD_ERR_EXTENSION => 'PHP-расширение остановило загрузку файла'
    ];

    /**
     * @return mixed|string
     */
    public function getErrorMessage()
    {
        return isset(self::$errorMessages[$this->error]) ?
            self::$errorMessages[$this->error] : 'Неизвестная ошибка';
    }
}
