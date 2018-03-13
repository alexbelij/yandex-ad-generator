<?php

namespace app\lib\tasks;

use app\lib\shuffleGroupStrategies\AbstractShuffleGroupStrategy;
use app\models\AdYandexGroup;
use yii\base\UnknownClassException;
use yii\helpers\Inflector;

/**
 * Class ShuffleGroupsTask
 * @package app\lib\tasks
 */
class ShuffleGroupsTask extends YandexBaseTask
{
    const TASK_NAME = 'ShuffleGroups';

    /**
     * @inheritDoc
     */
    public function execute($params = [])
    {
        $context = $this->task->getContext();

        $query = AdYandexGroup::find()
            ->andWhere([
                'status' => [AdYandexGroup::STATUS_PREACCEPTED, AdYandexGroup::STATUS_ACCEPTED],
                'serving_status' => AdYandexGroup::SERVING_STATUS_RARELY_SERVED
            ])
            ->andWhere(['>', 'keywords_count', 0]);

        if (!empty($context['shop_id'])) {
            $query
                ->innerJoinWith(['yandexAds.yandexCampaign'])
                ->andWhere(['shop_id' => $context['shop_id']]);
        }

        $this->createShuffleStrategy()->execute($query->all());
    }

    /**
     * @return AbstractShuffleGroupStrategy
     * @throws UnknownClassException
     */
    protected function createShuffleStrategy()
    {
        $namespace = 'app\lib\shuffleGroupStrategies\\';
        $shuffleStrategy = Inflector::camelize($this->shop->shuffle_strategy);

        $class = $namespace . $shuffleStrategy;

        if (!class_exists($class)) {
            throw new UnknownClassException($class);
        }

        return new $class(
            $this->getLogger(),
            $this->connection,
            $this->shop,
            $this->yandexOperationLogger
        );
    }
}
