<?php

namespace app\lib\tasks;

use app\components\LoggerInterface;
use app\events\listeners\SuccessImportFileListener;
use app\lib\import\ImportInterface;
use app\lib\import\XlsParser;
use app\lib\import\yml\TagParserFactory;
use app\lib\import\YmlParser;
use app\models\ExternalProduct;
use app\models\FileImport;
use yii\base\Event;
use yii\base\Exception;

/**
 * Задача на импорт файла
 *
 * Class UploadFileTask
 * @package app\lib\tasks
 */
class ImportFileTask extends AbstractTask
{
    const TASK_NAME = 'importFile';

    /**
     * @inheritDoc
     */
    public function execute($params = [])
    {
        $context = $this->task->getContext();
        $fileId = $context['file_id'];
        $fileImport = FileImport::findOne($fileId);

        if (!$fileImport) {
            throw new Exception('Файл для импорта не найден');
        }

        $consoleLogger = $this->getLogger();
        $parser = $this->createFileParser($fileImport, $consoleLogger);
        try {
            if ($parser->import($fileImport)) {

                ExternalProduct::updateAll(
                    ['is_available' => false],
                    [
                        'AND',
                        ['shop_id' => $fileImport->shop_id],
                        ['<', 'updated_at', $fileImport->created_at]
                    ]);

                $fileImport->is_loaded = true;
                $fileImport->save();
                \Yii::$app->trigger(SuccessImportFileListener::SUCCESS_FILE_IMPORT, new Event(['sender' => $this->task->shop]));
            }
        } finally {
            if (file_exists($fileImport->filename)) {
                unlink($fileImport->filename);
            }
        }
    }

    /**
     * @param FileImport $fileImport
     * @param LoggerInterface $logger
     * @return ImportInterface
     */
    protected function createFileParser(FileImport $fileImport, LoggerInterface $logger)
    {
        if (preg_match('#xlsx?$#', $fileImport->filename)) {
            return new XlsParser($logger);
        } else {
            $tagParserFactory = new TagParserFactory();
            return new YmlParser($tagParserFactory->create($fileImport, $logger, $this->task->shop), $logger);
        }
    }

    /**
     * @inheritDoc
     */
    protected function getLogFileName()
    {
        return 'import_file_' . $this->task->id;
    }
}
