<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 03.05.16
 * Time: 9:55
 */

namespace app\lib\tasks;

use app\helpers\JsonHelper;
use app\lib\api\yandex\direct\resources\AdGroupResource;
use app\lib\services\AdGroupService;
use app\lib\services\YandexService;
use app\models\AdYandexGroup;
use app\models\YandexCampaign;
use app\models\YandexUpdateLog;
use yii\helpers\ArrayHelper;

class UpdateGroupsStatusTask extends YandexBaseTask
{
    const TASK_NAME = 'UpdateGroupsStatus';

    /**
     * @var AdGroupService
     */
    protected $adGroupService;

    /**
     * @inheritdoc
     */
    protected function init()
    {
        parent::init();

        $adGroupResource = new AdGroupResource($this->connection);
        $this->adGroupService = new AdGroupService($adGroupResource);
    }


    /**
     * @inheritDoc
     */
    public function execute($params = [])
    {
        $context = $this->task->getContext();

        $query = AdYandexGroup::find()
            ->innerJoinWith(['yandexAds.yandexCampaign'])
            ->andWhere([YandexCampaign::fullColumn('shop_id') => $this->task->shop_id]);

        $this->logger->log('Начинаем синхронизацию статусов групп объявлений');
        $this->logger->log($query->createCommand()->getRawSql());

        foreach ($query->batch(ArrayHelper::getValue($context, 'batch_size', YandexService::MAX_CHUNK_SIZE)) as $groups) {
            $groups = ArrayHelper::index($groups, 'yandex_adgroup_id');
            $yandexGroupIds = array_keys($groups);

            $result = $this->adGroupService->findByIds($yandexGroupIds, ['Id', 'Status', 'ServingStatus']);

            foreach ($result->getItems() as $item) {
                $group = $groups[$item['Id']];

                $group->status = strtolower($item['Status']);
                $group->serving_status = strtolower($item['ServingStatus']);
                $group->save();

                if ($group->status == AdYandexGroup::SERVING_STATUS_RARELY_SERVED) {
                    $this->logger->log('Группа помеченна как "Мало показов": ' . JsonHelper::encodeModelPretty($group));
                    $this->logOperation($group, YandexUpdateLog::OPERATION_GROUP_RARELY_SERVED);
                }
            }
        }
    }
}
