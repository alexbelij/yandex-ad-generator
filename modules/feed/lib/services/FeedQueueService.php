<?php

namespace app\modules\feed\lib\services;

use app\modules\feed\lib\FeedReplacer;
use app\modules\feed\models\FeedQueue;
use yii\console\Exception;
use yii\db\Expression;

/**
 * Class FeedQueueService
 * @package app\modules\feed\lib\services
 */
class FeedQueueService
{
    /**
     * Обработка очереди
     *
     * @param FeedQueue $feedQueue
     * @throws Exception
     */
    public function execute(FeedQueue $feedQueue)
    {
        if ($feedQueue->status != FeedQueue::STATUS_PENDING) {
            throw new Exception('Статус должен быть pending');
        }

        $feedQueue->status = FeedQueue::STATUS_PROCESS;
        $feedQueue->save();

        $feedReplacer = new FeedReplacer($feedQueue);
        $feedQueue->result_file = tempnam($this->getTargetDir(), 'feed');

        try {
            $feedReplacer->replace($feedQueue->source_file, $feedQueue->result_file);
            $feedQueue->status = FeedQueue::STATUS_COMPLETED;
            $feedQueue->finished_at = date('Y-m-d H:i:s');
            $feedQueue->save();
            unlink($feedQueue->source_file);
        } catch (\Exception $e) {
            $feedQueue->status = FeedQueue::STATUS_ERROR;
            $feedQueue->error_message = $e->getMessage();
            $feedQueue->finished_at = date('Y-m-d H:i:s');
            $feedQueue->save();
            echo $e->getMessage() . PHP_EOL;
        }
    }

    /**
     * @return bool|string
     * @throws Exception
     */
    protected function getTargetDir()
    {
        $dir = \Yii::getAlias('@app/feeds');
        if (!file_exists($dir)) {
            if (!mkdir($dir)) {
                throw new Exception('Ошибка при создании директории - ' . $dir);
            }
        }

        return $dir;
    }
}
