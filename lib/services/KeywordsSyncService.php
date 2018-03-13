<?php

namespace app\lib\services;

use app\components\LoggerInterface;
use app\helpers\AccountHelper;
use app\lib\api\auth\ApiAccountIdentity;
use app\lib\api\yandex\direct\Connection;
use app\lib\api\yandex\direct\query\KeywordsQuery;
use app\lib\api\yandex\direct\resources\KeywordsResource;
use app\lib\Collection;
use app\models\Account;
use app\models\AdKeyword;
use app\models\AdYandexCampaign;
use app\models\YandexCampaign;

/**
 * Class KeywordsSyncService
 * @package app\lib\services
 */
class KeywordsSyncService
{
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var KeywordsResource
     */
    protected $keywordsResource;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * KeywordsSyncService constructor.
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->connection = new Connection();
        $this->connection->setTimeout(600);
        $this->keywordsResource = new KeywordsResource($this->connection);
        $this->logger = $logger;
    }

    /**
     * Проставление yandex id ключевым фразам
     *
     * @param null $shopId
     */
    public function updateYandexId($shopId = null)
    {
        $yaCampaignIds = YandexCampaign::find()
            ->andFilterWhere(['shop_id' => $shopId])
            ->select('yandex_id')
            ->column();

        list($accounts, $accountsCampaigns) = AccountHelper::groupCampaignsByAccountIds($yaCampaignIds);

        $fileName = $this->getFileName($shopId);

        if (!file_exists($fileName)) {
            file_put_contents($fileName, json_encode($accountsCampaigns));
        } else {
            $accountsCampaigns = json_decode(file_get_contents($fileName), true);
        }

        $toSave = $accountsCampaigns;

        foreach ($accountsCampaigns as $accountId => $campaignIds) {
            /** @var Account $account */
            $account = $accounts[$accountId];

            $this->logger->log(
                sprintf('Обновление ключевых фраз для аккаунта %s, количество кампаний: %d', $account->title,
                    count($campaignIds))
            );

            $this->connection->setAuthIdentity(new ApiAccountIdentity($account));
            $collection = new Collection($campaignIds);
            foreach ($collection->batch(10) as $ids) {
                $this->_processUpdateId($ids);
                foreach ($ids as $id) {
                    $ind = array_search($id, $toSave[$accountId]);
                    unset($toSave[$accountId][$ind]);
                }
                file_put_contents($fileName, json_encode($toSave));
            }
            unset($toSave[$accountId]);
            file_put_contents($fileName, json_encode($toSave));
            $this->logger->log(
                sprintf('Обновление фраз для аккаунта %s завершено', $account->title)
            );
        }
        unlink($fileName);
    }

    /**
     * @param array $ids
     */
    private function _processUpdateId(array $ids)
    {
        $limit = 10000;
        $offset = 0;
        $updCount = 0;
        $processedCount = 0;
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

            $this->logger->log(sprintf('Получено %d записей от директа', count($result->getItems())));

            foreach ($result->getItems() as $item) {
                $keyword = AdKeyword::find()
                    ->innerJoin(['adyc' => AdYandexCampaign::tableName()], 'adyc.ad_id = ad_keyword.ad_id')
                    ->andWhere(['keyword' => $item['Keyword']])
                    ->andWhere(['adyc.yandex_adgroup_id' => (int)$item['AdGroupId']])
                    ->one();
                if ($keyword) {
                    $keyword->yandex_id = $item['Id'];
                    $keyword->save();
                    $updCount++;
                }
                $processedCount++;
            }

            $offset += $limit;
        }
        $this->logger->log(sprintf('Обновлено: %d фраз', $updCount));
        $this->logger->log(sprintf('Всего обработано: %d фраз', $processedCount));

    }

    /**
     * @param null $shopId
     * @return bool|string
     */
    protected function getFileName($shopId = null)
    {
        return \Yii::getAlias("@app/runtime/commands/ad_sync_$shopId.json");
    }
}
