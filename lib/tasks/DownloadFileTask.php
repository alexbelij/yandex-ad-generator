<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 03.09.16
 * Time: 11:13
 */

namespace app\lib\tasks;
use app\helpers\RemoteFileHelper;
use app\models\FileImport;
use app\models\TaskQueue;
use yii\base\Exception;

/**
 * Class UploadFileTask
 * @package app\lib\tasks
 */
class DownloadFileTask extends AbstractTask
{
    const TASK_NAME = 'downloadFile';

    /**
     * @inheritDoc
     */
    public function execute($params = [])
    {
        $shop = $this->task->shop;

        $fileName = RemoteFileHelper::downloadFile($shop->remote_file_url);

        \Yii::$app->db->close();
        \Yii::$app->db->open();

        $fileImport = new FileImport([
            'original_filename' => $shop->remote_file_url,
            'filename' => $fileName,
            'size' => filesize($fileName),
            'shop_id' => $shop->id
        ]);
        $fileImport->save();

        $res = TaskQueue::createNewTask($shop->primaryKey, ImportFileTask::TASK_NAME, ['file_id' => $fileImport->primaryKey]);

        if (!$res) {
            throw new Exception('Ошибка при создании задачи на импорт файла');
        }
    }
}
