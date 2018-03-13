<?php

namespace app\lib\reports;

use app\helpers\ArrayHelper;
use app\models\GeneratorSettings;
use app\models\search\ProductsSearch;
use app\models\TaskQueue;

/**
 * Class AdUpdateReport
 * @package app\lib\reports
 */
class AdUpdateReport implements ReportInterface
{
    /**
     * @inheritDoc
     */
    public function send($params = [])
    {
        /** @var TaskQueue $task */
        $task = $params['task'];

        $tplVars = [
            'task' => $task,
            'withoutTitleCount' => $this->getAdCountWithoutTitle($task)
        ];

        \Yii::$app->mailer->compose('tasks/ad_update_report', $tplVars)
            ->setTo(\Yii::$app->params['report']['to'])
            ->setFrom(\Yii::$app->params['report']['from'])
            ->setSubject("Обновление {$task->shop->name} {$task->completed_at}")
            ->send();
    }

    /**
     * @param TaskQueue $task
     * @return mixed
     */
    protected function getAdCountWithoutTitle(TaskQueue $task)
    {
        $searchModel = $this->getProductSearch($task);
        $searchModel->withoutAd = true;
        $searchModel->onlyActive = true;

        //echo $searchModel->search()->query->createCommand()->getRawSql() . PHP_EOL;

        return $searchModel->search()->query->count();
    }

    /**
     * Возвращает поисковую модель для поиска товаров
     *
     * @param TaskQueue $task
     * @return ProductsSearch
     */
    protected function getProductSearch(TaskQueue $task)
    {
        $searchModel = new ProductsSearch();
        $searchModel->shopId = $task->shop_id;

        /** @var GeneratorSettings $generatorSettings */
        $generatorSettings = GeneratorSettings::find()
            ->where(['shop_id' => $task->shop_id])
            ->one();

        $context = $task->getContext();

        $searchModel->load($context, '');

        if (isset($context['brandIds'])) {
            $brandIds = $context['brandIds'];
        } else {
            $brandIds = !empty($generatorSettings->brands) ?
                explode(',', $generatorSettings->brands) : [];
        }

        if (!empty($brandIds)) {
            $searchModel->brandId = $brandIds;
        }

        $categoriesIds = ArrayHelper::getValue($context, 'categoryIds', $generatorSettings->getCategoryIds());
        if (!empty($categoriesIds)) {
            $searchModel->categoryId = $categoriesIds;
        }

        return $searchModel;
    }
}
