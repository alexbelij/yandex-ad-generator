<?php

namespace app\lib\tasks;

use app\helpers\AccountHelper;
use app\helpers\ArrayHelper;
use app\lib\api\auth\ApiAccountIdentity;
use app\lib\api\yandex\direct\query\KeywordsQuery;
use app\lib\api\yandex\direct\resources\KeywordsResource;
use app\lib\Collection;
use app\lib\LoggedStub;
use app\models\Account;
use app\models\AdKeyword;
use app\models\YandexCampaign;
use app\models\YandexUpdateLog;

/**
 * Class KeywordsSyncTask
 * @package app\lib\tasks
 */
class KeywordsSyncTask extends YandexBaseTask
{
    const TASK_NAME = 'keywordsSync';

    /**
     * @var KeywordsResource
     */
    protected $keywordsResource;

    /**
     * @inheritDoc
     */
    protected function init()
    {
        parent::init();
        $this->keywordsResource = new KeywordsResource($this->connection);
        $this->connection->setTimeout(600);
    }

    /**
     * @inheritDoc
     */
    public function execute($params = [])
    {
        $this->syncKeywords($this->task->shop_id);
    }

    /**
     * @param null|int $shopId
     */
    private function syncKeywords($shopId = null)
    {
        $yaCampaignIds = YandexCampaign::find()
            ->andFilterWhere(['shop_id' => $shopId])
            ->select('yandex_id')
            ->column();

        list($accounts, $accountsCampaigns) = AccountHelper::groupCampaignsByAccountIds($yaCampaignIds);

        foreach ($accountsCampaigns as $accountId => $campaignIds) {
            /** @var Account $account */
            $account = $accounts[$accountId];

            $this->logger->log(
                sprintf('Обновление ключевых фраз для аккаунта %s, количество кампаний: %d', $account->title, count($campaignIds))
            );

            $this->connection->setAuthIdentity(new ApiAccountIdentity($account));
            $collection = new Collection($campaignIds);
            try {
                $this->syncKeywordsCampaigns($collection);
            } catch (\Exception $e) {
                $this->yandexOperationLogger->log(
                    new LoggedStub(['id' => null, 'type' => 'info']),
                    YandexUpdateLog::OPERATION_INFO,
                    YandexUpdateLog::STATUS_ERROR,
                    $e->getMessage()
                );
            }

            $this->logger->log(
                sprintf('Обновление фраз для аккаунта %s завершено', $account->title)
            );
        }
    }

    /**
     * @param Collection $collection
     */
    private function syncKeywordsCampaigns(Collection $collection)
    {
        $limit = 10000;
        foreach ($collection->batch(10) as $ids) {
            $offset = 0;
            $updCount = 0;
            $processedCount = 0;

            $campaigns = YandexCampaign::find()
                ->andWhere(['yandex_id' => $ids])
                ->all();

            $this->yandexOperationLogger->log(
                new LoggedStub(['id' => null, 'type' => 'info']),
                YandexUpdateLog::OPERATION_INFO,
                YandexUpdateLog::STATUS_SUCCESS,
                sprintf(
                    'Обработка кампаний %s',
                    implode(', ', ArrayHelper::getColumn($campaigns, 'title'))
                )
            );

            while (true) {
                $query = new KeywordsQuery([
                    'campaignIds' => $ids,
                ]);
                $query->setLimit($limit);
                $query->setOffset($offset);

                $this->logger->log(sprintf('Запрос: %s', json_encode($query->getQuery())));

                $result = $this->keywordsResource->find($query);

                if (!$result->getItems()) {
                    break;
                }

                $resultIndexed = [];
                $yandexKeywordIds = [];
                foreach ($result->getItems() as $item) {
                    $yandexKeywordIds[] = $item['Id'];
                    $resultIndexed[$item['Id']] = $item;
                }

                if (empty($yandexKeywordIds)) {
                    $offset += $limit;
                    continue;
                }

                $existsKeywordIds = AdKeyword::find()
                    ->select('yandex_id')
                    ->andWhere(['yandex_id' => $yandexKeywordIds])
                    ->column();

                $toDeleteIds = array_diff($yandexKeywordIds, $existsKeywordIds);
                $this->yandexOperationLogger->log(
                    new LoggedStub(['id' => null, 'type' => 'info']),
                    YandexUpdateLog::OPERATION_INFO,
                    YandexUpdateLog::STATUS_SUCCESS,
                    sprintf('Будет удалено %d фраз', count($toDeleteIds))
                );

                if (!empty($toDeleteIds)) {
                    $this->deleteKeywords($toDeleteIds, $resultIndexed);
                }

                $offset += $limit;
            }
            $this->logger->log(sprintf('Обновлено: %d фраз', $updCount));
            $this->logger->log(sprintf('Всего обработано: %d фраз', $processedCount));
        }
    }

    /**
     * Удаление ключевых фраз из директа
     *
     * @param array $toDeleteIds
     * @param array $resultIndexed
     */
    private function deleteKeywords(array $toDeleteIds, array $resultIndexed)
    {
        $deleteResult = $this->keywordsResource->delete($toDeleteIds);
        foreach ($deleteResult->getIds() as $deletedId) {
            if (isset($resultIndexed[$deletedId])) {
                $deletedItem = $resultIndexed[$deletedId];
                $stub = new LoggedStub([
                    'id' => $deletedId,
                    'type' => 'keyword'
                ]);
                $this->yandexOperationLogger->log(
                    $stub,
                    YandexUpdateLog::OPERATION_REMOVE,
                    YandexUpdateLog::STATUS_SUCCESS,
                    sprintf('Удаление фразы: "%s"', $deletedItem['Keyword'])
                );
            }
        }
    }

    /**
     * @inheritDoc
     */
    protected function getLogFileName()
    {
        return 'keywords_sync_' . $this->task->id;
    }
}
