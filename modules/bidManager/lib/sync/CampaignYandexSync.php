<?php

namespace app\modules\bidManager\lib\sync;

use app\helpers\ArrayHelper;
use app\lib\api\auth\ApiAccountIdentity;
use app\lib\api\yandex\direct\query\CampaignQuery;
use app\modules\bidManager\models\Account;
use app\modules\bidManager\models\YandexCampaign;
use yii\base\Exception;

/**
 * Class CampaignYandexSync
 * @package app\modules\bidManager\lib\sync
 */
class CampaignYandexSync extends AbstractYandexSync
{
    /**
     * @inheritDoc
     */
    public function sync(Account $account)
    {
        $this->connection->setAuthIdentity(new ApiAccountIdentity($account));
        $this->syncCampaigns($account);
    }

    /**
     * Обновление кампаний аккаунта
     *
     * @param Account $account
     * @throws Exception
     */
    protected function syncCampaigns(Account $account)
    {
        $limit = 9999;
        $offset = 0;
        $this->logger->info('Начинаем синхронизацию кампаний');
        $count = 0;
        $startTime = time();

        $query = new CampaignQuery([
            'states' => ['ON']
        ]);

        while (true) {
            $query->setLimit($limit);
            $query->setOffset($offset);
            $campaignResult = $this->campaignResource->find($query);
            $this->logger->info("Получено {$campaignResult->count()} кампаний");

            foreach ($campaignResult->getItems() as $campaignData) {
                $campaign = $this->mapCampaign($campaignData, $account);
                if (!$campaign->save()) {
                    throw new Exception('Ошибка при сохранении: ' . ArrayHelper::first($campaign->getFirstErrors()));
                }
            }
            $count += $campaignResult->count();
            if ($campaignResult->count() < $limit) {
                break;
            }
            $offset += $limit;
        }
        $this->logger->info("Обработано $count кампаний");

        YandexCampaign::deleteAll('updated_at < :date', [':date' => date('Y-m-d H:i:s', $startTime)]);
    }

    /**
     * @param array $campaignData
     * @param Account $account
     * @return YandexCampaign
     */
    protected function mapCampaign(array $campaignData, Account $account)
    {
        $campaign = YandexCampaign::findOne($campaignData['Id']);
        if (!$campaign) {
            $campaign = new YandexCampaign();
        }
        $campaign->id = $campaignData['Id'];
        $campaign->title = $campaignData['Name'];
        $campaign->start_date = $campaignData['StartDate'];
        $campaign->end_date = $campaignData['EndDate'];
        $campaign->account_id = $account->id;
        $campaign->client_info = $campaignData['ClientInfo'];
        $campaign->currency = $campaignData['Currency'];
        $campaign->status = $campaignData['Status'];
        $campaign->state = $campaignData['State'];
        $campaign->status_payment = $campaignData['StatusPayment'];
        $campaign->status_clarification = $campaignData['StatusClarification'];
        $campaign->stat_clicks = ArrayHelper::getValue($campaignData, 'Statistics.Clicks');
        $campaign->stat_impressions = ArrayHelper::getValue($campaignData, 'Statistics.Impressions');
        $campaign->funds_mode = ArrayHelper::getValue($campaignData, 'Funds.Mode');
        $campaign->funds_balance = $this->convertPrice(ArrayHelper::getValue($campaignData, 'Funds.CampaignFunds.Balance'));
        $campaign->funds_sum = $this->convertPrice(ArrayHelper::getValue($campaignData, 'Funds.CampaignFunds.Sum'));
        $campaign->funds_shared_refund = $this->convertPrice(ArrayHelper::getValue($campaignData, 'Funds.SharedAccountFunds.Refund'));
        $campaign->funds_shared_spend = $this->convertPrice(ArrayHelper::getValue($campaignData, 'Funds.SharedAccountFunds.Spend'));

        return $campaign;
    }
}
