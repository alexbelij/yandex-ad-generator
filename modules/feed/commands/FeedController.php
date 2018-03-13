<?php

namespace app\modules\feed\commands;

use app\modules\feed\lib\services\FeedQueueService;
use app\modules\feed\models\FeedQueue;
use yii\console\Controller;

/**
 * Class FeedController
 * @package app\modules\feed\commands
 */
class FeedController extends Controller
{
    /**
     * Обработка "очереди" фидов
     */
    public function actionQueue()
    {
        $processedCount = FeedQueue::find()
            ->andWhere(['status' => FeedQueue::STATUS_PROCESS])
            ->count();

        if ($processedCount > 0) {
            echo 'Допускается обработка только одно файла' . PHP_EOL;
            die;
        }

        /** @var FeedQueue $feed */
        $feed = FeedQueue::find()
            ->andWhere(['status' => FeedQueue::STATUS_PENDING])
            ->orderBy(['id' => SORT_ASC])
            ->one();

        $feedQueueService = new FeedQueueService();

        if ($feed) {
            $feedQueueService->execute($feed);
        }
    }
}
