<?php

namespace app\commands;

use app\helpers\AccountHelper;
use app\lib\tasks\DownloadFileTask;
use app\lib\tasks\ProductValidateTask;
use app\lib\tasks\ShuffleGroupsTask;
use app\lib\tasks\UpdateGroupsStatusTask;
use app\lib\tasks\YandexUpdateTask;
use app\models\Account;
use app\models\GeneratorSettings;
use app\models\Shop;
use app\models\TaskQueue;
use Cron\CronExpression;
use yii\console\Controller;

/**
 * Class SchedulerController
 * @package app\commands
 */
class SchedulerController extends Controller
{
    /**
     * Управление задачами на скачивание файлов yml по установленному расписанию
     */
    public function actionDownloadYml()
    {
        /** @var Shop[] $shops */
        $shops = Shop::find()
            ->andWhere(['is_import_schedule' => true])
            ->all();

        $time = new \DateTime();
        foreach ($shops as $shop) {
            $cronExpr = CronExpression::factory($shop->schedule);

            if ($cronExpr->isDue($time)) {
                if (!TaskQueue::hasActiveTask($shop, DownloadFileTask::TASK_NAME, $time->getTimestamp())) {
                    TaskQueue::createNewTask($shop->id, DownloadFileTask::TASK_NAME);
                }
            }
        }
    }

    /**
     * Запланировать валидацию ссылок на товары
     *
     * @param null|int $shopId
     */
    public function actionLinkAvailable($shopId = null)
    {
        /** @var Shop[] $shops */
        $shops = Shop::find()->andFilterWhere([
            'id' => $shopId,
            'is_link_validation' => true
        ])->all();

        foreach ($shops as $shop) {
            if (!TaskQueue::hasActiveTasks($shop->id, ProductValidateTask::TASK_NAME)) {
                TaskQueue::createNewTask($shop->id, ProductValidateTask::TASK_NAME);
            }
        }
    }

    /**
     * Использовать перемещение объявлений между группами
     *
     * @param null|int $shopId
     */
    public function actionShuffleGroups($shopId = null)
    {
        /** @var Shop[] $shops */
        $shops = Shop::find()->andFilterWhere([
            'id' => $shopId,
            'is_shuffle_groups' => true
        ])->all();

        foreach ($shops as $shop) {
            if (!TaskQueue::hasActiveTasks($shop->id, ShuffleGroupsTask::TASK_NAME)) {
                TaskQueue::createNewTask($shop->id, ShuffleGroupsTask::TASK_NAME);
            }
        }
    }

    /**
     * Обновление статуса групп объявлений
     *
     * @param null|int $shopId
     */
    public function actionUpdateGroupsStatus($shopId = null)
    {
        /** @var Shop[] $shops */
        $shops = Shop::find()->andFilterWhere([
            'id' => $shopId,
            'is_shuffle_groups' => true
        ])->all();

        foreach ($shops as $shop) {
            if (!TaskQueue::hasActiveTasks($shop->id, UpdateGroupsStatusTask::TASK_NAME)) {
                TaskQueue::createNewTask($shop->id, UpdateGroupsStatusTask::TASK_NAME);
            }
        }
    }

    /**
     * Автообновление
     * @param null $shopId
     */
    public function actionAutoupdate($shopId = null)
    {
        /** @var Shop[] $shops */
        $shops = Shop::find()
            ->andFilterWhere([
                'is_autoupdate' => true,
                'id' => $shopId
            ])
            ->all();

        $time = new \DateTime();

        foreach ($shops as $shop) {

            $cronExpr = CronExpression::factory($shop->schedule_autoupdate);

            if (!TaskQueue::hasActiveTasks($shop->id, YandexUpdateTask::TASK_NAME) && $cronExpr->isDue($time)) {
                /** @var GeneratorSettings $generatorSettings */
                $generatorSettings = GeneratorSettings::find()->andWhere(['shop_id' => $shop->id])->one();

                $brandIds = $generatorSettings->brands ? explode(',', $generatorSettings->brands) : [];
                $categoryIds = $generatorSettings->categoryIds;

                if (empty($brandIds)) {
                    continue;
                }

                $context = [
                    'reportName' => 'ad_update_report',
                    'brandIds' => $brandIds,
                    'categoryIds' => $categoryIds,
                    'priceFrom' => $generatorSettings->price_from,
                    'priceTo' => $generatorSettings->price_to
                ];

                $accountIds = AccountHelper::getAccountIds($shop, $brandIds);
                /** @var Account[] $accounts */
                $accounts = Account::find()->andWhere(['id' => $accountIds])->all();

                foreach ($accounts as $account) {
                    $context['accountId'] = $account->id;
                    if (TaskQueue::hasActiveTasks($shop->id, YandexUpdateTask::TASK_NAME, ['accountId' => $account->id])) {
                        continue;
                    }

                    TaskQueue::createNewTask(
                        $shop->id,
                        YandexUpdateTask::TASK_NAME,
                        $context,
                        "Аккаунт - {$account->title}, id - {$account->id}",
                        ['accountId']
                    );
                }
            }
        }
    }

    /**
     * @param $taskName
     * @param $shopId
     */
    public function actionAddNewTask($taskName, $shopId = null)
    {
        /** @var Shop[] $shops */
        $shops = Shop::find()->andFilterWhere([
            'id' => $shopId ? explode(',', $shopId) : null,
        ])->all();

        foreach ($shops as $shop) {
            if (!TaskQueue::hasActiveTasks($shop->id, $taskName)) {
                TaskQueue::createNewTask($shop->id, $taskName);
            }
        }
    }
}
