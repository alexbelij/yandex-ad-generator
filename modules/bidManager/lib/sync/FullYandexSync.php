<?php

namespace app\modules\bidManager\lib\sync;

use app\helpers\ArrayHelper;
use app\lib\api\auth\ApiAccountIdentity;
use app\lib\api\yandex\direct\query\AdGroupQuery;
use app\lib\api\yandex\direct\query\BidQuery;
use app\lib\api\yandex\direct\query\KeywordsQuery;
use app\modules\bidManager\models\Account;
use app\modules\bidManager\models\YandexAdGroup;
use app\modules\bidManager\models\YandexBid;
use app\modules\bidManager\models\YandexCampaign;
use app\modules\bidManager\models\YandexKeyword;
use yii\console\Exception;

/**
 * Сервис обновления
 *
 * Class YandexUpdateService
 * @package app\modules\bidManager\lib\services
 */
class FullYandexSync extends AbstractYandexSync
{
    /**
     * @inheritdoc
     */
    public function sync(Account $account)
    {
        $this->connection->setAuthIdentity(new ApiAccountIdentity($account));
        $this->syncAdGroups($account);
        $this->syncKeywords($account);
        $this->syncBids($account);

        $this->updateAccount($account);
    }

    /**
     * Синхронизация групп объявлений
     *
     * @param Account $account
     * @throws Exception
     */
    protected function syncAdGroups(Account $account)
    {
        $campaignQuery = YandexCampaign::find()
            ->select('id')
            ->asArray()
            ->andWhere(['account_id' => $account->id]);

        $startTime = time();
        $count = 0;
        $this->logger->info('Начинаем синхронизацию групп объявлений');
        foreach ($campaignQuery->batch(10) as $campaigns) {
            $campaignIds = ArrayHelper::getColumn($campaigns, 'id');
            $this->logger->info('Синхронизируем группы для кампаний: ' . implode(', ', $campaignIds));
            $adGroupQuery = new AdGroupQuery(['campaignIds' => $campaignIds]);
            $limit = 9999;
            $offset = 0;

            while (true) {
                $adGroupQuery
                    ->setOffset($offset)
                    ->setLimit($limit);

                $adGroupResult = $this->adGroupResource->find($adGroupQuery);
                $this->logger->info("Получено групп: " . $adGroupResult->count());

                foreach ($adGroupResult->getItems() as $adGroupData) {
                    $adGroup = $this->mapAdGroup($adGroupData, $account);
                    if (!$adGroup->save()) {
                        throw new Exception('Ошибка при сохранении: ' . ArrayHelper::first($adGroup->getFirstErrors()));
                    }
                }
                $count += $adGroupResult->count();
                if ($adGroupResult->count() < $limit) {
                    break;
                }
                $offset += $limit;
            }
        }
        $this->logger->info('Всего обновленно групп: ' . $count);
        YandexAdGroup::deleteAll('updated_at < :date', [':date' => date('Y-m-d H:i:s', $startTime)]);
    }

    /**
     * @param Account $account
     * @throws Exception
     */
    protected function syncKeywords(Account $account)
    {
        $campaignQuery = YandexCampaign::find()
            ->select('id')
            ->asArray()
            ->andWhere(['account_id' => $account->id]);

        $count = 0;
        $startTime = time();
        $this->logger->info('Начинаем синхронизацию ключевых слов');
        foreach ($campaignQuery->batch(10) as $campaigns) {
            $campaignIds = ArrayHelper::getColumn($campaigns, 'id');
            $this->logger->info('Синхронизируем ключевые слова для кампаний: ' . implode(', ', $campaignIds));
            $keywordsQuery = new KeywordsQuery(['campaignIds' => $campaignIds]);
            $limit = 9999;
            $offset = 0;

            while (true) {
                $keywordsQuery
                    ->setOffset($offset)
                    ->setLimit($limit);

                $keywordsResult = $this->keywordsResource->find($keywordsQuery);
                $this->logger->info("Получено ключевиков: " . $keywordsResult->count());

                foreach ($keywordsResult->getItems() as $keywordData) {
                    $keyword = $this->mapKeyword($keywordData, $account);

                    if (!$keyword->save()) {
                        throw new Exception('Ошибка при сохранении: ' . ArrayHelper::first($keyword->getFirstErrors()));
                    }
                }
                $count += $keywordsResult->count();
                if ($keywordsResult->count() < $limit) {
                    break;
                }
                $offset += $limit;
            }
        }
        $this->logger->info('Всего обновленно ключевых фраз: ' . $count);
        YandexKeyword::deleteAll('updated_at < :date', [':date' => date('Y-m-d H:i:s', $startTime)]);
    }
}
