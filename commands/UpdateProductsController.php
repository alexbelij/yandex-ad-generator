<?php

namespace app\commands;

use app\lib\tasks\UpdateProductsTask;
use app\models\Shop;
use app\models\TaskQueue;
use yii\console\Controller;

/**
 * Class UpdateProductsController
 * @package app\commands
 */
class UpdateProductsController extends Controller
{
    /**
     * @param null|int $shopId
     */
    public function actionIndex($shopId = null)
    {
        $query = Shop::find()
            ->andWhere(['external_strategy' => Shop::EXTERNAL_STRATEGY_API]);

        if ($shopId) {
            $query->andWhere(['id' => $shopId]);
        }

        /** @var Shop[] $shops */
        $shops = $query->all();

        foreach ($shops as $shop) {
            if (!TaskQueue::hasActiveTasks($shop->id, UpdateProductsTask::TASK_NAME)) {
                TaskQueue::createNewTask($shop->id, UpdateProductsTask::TASK_NAME);
            }
        }
    }
}
