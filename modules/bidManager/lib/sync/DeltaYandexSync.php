<?php

namespace app\modules\bidManager\lib\sync;

use app\helpers\ArrayHelper;
use app\lib\api\auth\ApiAccountIdentity;
use app\lib\api\yandex\direct\query\BidQuery;
use app\lib\api\yandex\direct\query\ChangesQuery;
use app\lib\api\yandex\direct\query\CheckResult;
use app\lib\api\yandex\direct\query\KeywordsQuery;
use app\lib\api\yandex\direct\resources\ChangesResource;
use app\modules\bidManager\models\Account;
use app\modules\bidManager\models\YandexAdGroup;
use app\modules\bidManager\models\YandexBid;
use app\modules\bidManager\models\YandexCampaign;
use app\modules\bidManager\models\YandexKeyword;

/**
 * Class DeltaYandexSync
 * @package app\modules\bidManager\lib\sync
 */
class DeltaYandexSync extends AbstractYandexSync
{
    /**
     * @var string
     */
    protected $timeStamp;

    /**
     * @return string
     */
    public function getTimeStamp()
    {
        return $this->timeStamp;
    }

    /**
     * @param string $timeStamp
     */
    public function setTimeStamp($timeStamp)
    {
        $this->timeStamp = $timeStamp;
    }

    /**
     * @inheritDoc
     */
    public function sync(Account $account)
    {
        $this->logger->info('Начинаем синхронизацию изменений');
        $this->connection->setAuthIdentity(new ApiAccountIdentity($account));
        $changesResult = $this->getChanges($account);
        $modifiedAdGroupIds = $changesResult->getModified(CheckResult::ADGROUPS);

        $this->logger->info('Получено изменений групп: ' . count($modifiedAdGroupIds));

        if (!empty($modifiedAdGroupIds)) {
            $offset = 0;
            $limit = 1000;
            while ($adGroupIds = array_slice($modifiedAdGroupIds, $offset, $limit)) {
                $this->syncAdGroups($adGroupIds, $account);
                $this->syncKeywords($adGroupIds, $account);
                $offset += $limit;
            }
        }

        $this->syncBids($account);

        if (count($changesResult->getNotFound(CheckResult::ADGROUPS)) > 0) {
            YandexKeyword::deleteAll(['group_id' => $changesResult->getNotFound(CheckResult::ADGROUPS)]);
            YandexBid::deleteAll(['group_id' => $changesResult->getNotFound(CheckResult::ADGROUPS)]);
            YandexAdGroup::deleteAll(['id' => $changesResult->getNotFound(CheckResult::ADGROUPS)]);
        }

        $this->updateAccount($account);
        $this->logger->info('Синхронизация завершена');
    }

    /**
     * @param array $adGroupIds
     * @param Account $account
     */
    protected function syncAdGroups(array $adGroupIds, Account $account)
    {
        $this->logger->info('Начинаем обновление групп');
        $adGroupResult = $this->adGroupResource->findByIds($adGroupIds);
        $this->logger->info('Получено ' . $adGroupResult->count() . ' групп');

        foreach ($adGroupResult->getItems() as $adGroupData) {
            $adGroup = $this->mapAdGroup($adGroupData, $account);
            if (!$adGroup->save()) {
                $this->logger->info('Ошибка при сохранении группы: ' . ArrayHelper::first($adGroup->getFirstErrors()));
            }
        }
        $this->logger->info('Синхронизация групп завершена');
    }

    /**
     * @param array $adGroupIds
     * @param Account $account
     */
    protected function syncKeywords(array $adGroupIds, Account $account)
    {
        $this->logger->info('Начинаем обновление ключевиков');
        $query = new KeywordsQuery([
            'adGroupIds' => $adGroupIds
        ]);

        $keywordResult = $this->keywordsResource->find($query);
        $this->logger->info('Получено ' . $keywordResult->count() . ' записей');

        foreach ($keywordResult->getItems() as $keywordData) {
            $keyword = $this->mapKeyword($keywordData, $account);
            if (!$keyword->save()) {
                $this->logger->info('Ошибка при сохранении группы: ' . ArrayHelper::first($keyword->getFirstErrors()));
            }
        }

        $this->logger->info('Синхронизация ключевиков завершена');
    }

    /**
     * Возвращает id измененных данных, произошедших с момента timeStamp
     *
     * @param Account $account
     * @return CheckResult
     */
    protected function getChanges(Account $account)
    {
        $changesResource = new ChangesResource($this->connection);

        $campaignIds = YandexCampaign::find()
            ->select('id')
            ->andWhere(['account_id' => $account->id])
            ->column();

        if (!$this->timeStamp) {
            $timeStamp = strtotime($account->last_updated_at);
        } else {
            $timeStamp = $this->timeStamp;
        }

        if (!$timeStamp) {
            $timeStamp = strtotime('-1 hour');
        }

        $changesQuery = new ChangesQuery([], [
            'timestamp' => gmdate('Y-m-d\TH:i:s\Z', $timeStamp),
            'campaignIds' => $campaignIds
        ]);

        return $changesResource->check($changesQuery, ['AdGroupIds', 'CampaignIds']);
    }
}
