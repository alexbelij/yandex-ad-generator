<?php

namespace app\modules\bidManager\lib\sync;

use app\lib\api\yandex\direct\events\UnitUpdateListener;
use app\lib\api\yandex\direct\query\BidQuery;
use app\modules\bidManager\models\YandexCampaign;
use Psr\Log\LoggerInterface;
use app\helpers\ArrayHelper;
use app\lib\api\yandex\direct\Connection;
use app\lib\api\yandex\direct\resources\AdGroupResource;
use app\lib\api\yandex\direct\resources\BidResource;
use app\lib\api\yandex\direct\resources\CampaignResource;
use app\lib\api\yandex\direct\resources\KeywordsResource;
use app\lib\Logger;
use app\modules\bidManager\models\Account;
use app\modules\bidManager\models\AdSearchPrice;
use app\modules\bidManager\models\AuctionBid;
use app\modules\bidManager\models\ContextCoverage;
use app\modules\bidManager\models\YandexAdGroup;
use app\modules\bidManager\models\YandexBid;
use app\modules\bidManager\models\YandexKeyword;
use Exception;

/**
 * Class AbstractYandexSync
 * @package app\modules\bidManager\lib\sync
 */
abstract class AbstractYandexSync
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var CampaignResource
     */
    protected $campaignResource;

    /**
     * @var AdGroupResource
     */
    protected $adGroupResource;

    /**
     * @var KeywordsResource
     */
    protected $keywordsResource;

    /**
     * @var BidResource
     */
    protected $bidResource;

    /**
     * @inheritDoc
     */
    public function __construct(LoggerInterface $logger = null)
    {
        $this->connection = new Connection();
        $this->connection->setTimeout(600);
        $unitUpdateListener = new UnitUpdateListener();
        $this->connection->on(Connection::EVENT_AFTER_REQUEST, [$unitUpdateListener, 'update']);
        $this->campaignResource = new CampaignResource($this->connection);
        $this->adGroupResource = new AdGroupResource($this->connection);
        $this->keywordsResource = new KeywordsResource($this->connection);
        $this->bidResource = new BidResource($this->connection);

        if (!$logger) {
            $logger = new Logger();
        }
        $this->logger = $logger;
    }

    /**
     * Метод синхронизации
     *
     * @param Account $account
     * @return mixed
     */
    abstract public function sync(Account $account);

    /**
     * @param array $adGroupData
     * @param Account $account
     * @return YandexAdGroup
     */
    protected function mapAdGroup(array $adGroupData, Account $account)
    {
        $adGroup = YandexAdGroup::findOne($adGroupData['Id']);
        if (!$adGroup) {
            $adGroup = new YandexAdGroup();
        }
        $adGroup->id = $adGroupData['Id'];
        $adGroup->account_id = $account->id;
        $adGroup->campaign_id = $adGroupData['CampaignId'];
        $adGroup->name = $adGroupData['Name'];
        $adGroup->status = $adGroupData['Status'];
        $adGroup->type = $adGroupData['Type'];

        return $adGroup;
    }

    /**
     * @param array $keywordData
     * @param Account $account
     * @return YandexKeyword
     */
    protected function mapKeyword(array $keywordData, Account $account)
    {
        $keyword = YandexKeyword::findOne($keywordData['Id']);
        if (!$keyword) {
            $keyword = new YandexKeyword();
        }
        $keyword->id = $keywordData['Id'];
        $keyword->keyword = $keywordData['Keyword'];
        $keyword->account_id = $account->id;
        $keyword->campaign_id = $keywordData['CampaignId'];
        $keyword->status = $keywordData['Status'];
        $keyword->state = $keywordData['State'];
        $keyword->group_id = $keywordData['AdGroupId'];
        $keyword->bid = $this->convertPrice($keywordData['Bid']);
        $keyword->context_bid = $this->convertPrice($keywordData['ContextBid']);
        $keyword->stat_search_clicks = ArrayHelper::getValue($keywordData, 'StatisticsSearch.Clicks');
        $keyword->stat_search_impressions = ArrayHelper::getValue($keywordData, 'StatisticsSearch.Impressions');
        $keyword->stat_network_clicks = ArrayHelper::getValue($keywordData, 'StatisticsNetwork.Clicks');
        $keyword->stat_network_impressions = ArrayHelper::getValue($keywordData, 'StatisticsNetwork.Impressions');

        return $keyword;
    }

    /**
     * Синхронизация ставок
     *
     * @param Account $account
     * @throws Exception
     */
    protected function syncBids(Account $account)
    {
        $campaignQuery = YandexCampaign::find()
            ->select('id')
            ->asArray()
            ->andWhere(['account_id' => $account->id]);

        $count = 0;
        $startTime = time();
        $this->logger->info('Начинаем синхронизацию ставок');
        foreach ($campaignQuery->batch(10) as $campaigns) {
            $campaignIds = ArrayHelper::getColumn($campaigns, 'id');
            $this->logger->info('Синхронизируем ставки для кампаний: ' . implode(', ', $campaignIds));
            $adQuery = new BidQuery(['campaignIds' => $campaignIds]);
            $limit = 9999;
            $offset = 0;

            while (true) {
                $adQuery
                    ->setOffset($offset)
                    ->setLimit($limit);

                $bidResult = $this->bidResource->find($adQuery);
                $this->logger->info("Получено ставок: " . $bidResult->count());

                foreach ($bidResult->getItems() as $bidData) {
                    $count += $this->updateBid($bidData, $account);
                }

                if ($bidResult->count() < $limit) {
                    break;
                }
                $offset += $limit;
            }
        }
        $this->logger->info('Всего обновленно ставок: ' . $count);
        YandexBid::deleteAll('updated_at < :date', [':date' => date('Y-m-d H:i:s', $startTime)]);
    }

    /**
     * @param array $bidData
     * @param Account $account
     * @return int
     * @throws Exception
     */
    protected function updateBid(array $bidData, Account $account)
    {
        $bid = YandexBid::findOne([
            'campaign_id' => $bidData['CampaignId'],
            'group_id' => $bidData['AdGroupId'],
            'keyword_id' => $bidData['KeywordId']
        ]);
        if (!$bid) {
            $bid = new YandexBid();
        }

        $competitorsBids = json_encode(array_map(function ($val) {
            return $val / 1000000;
        }, ArrayHelper::getValue($bidData, 'CompetitorsBids', [])));

        if (
            $bid->id
            && $bid->context_bid == $this->convertPrice($bidData['ContextBid'])
            && $bid->bid == $this->convertPrice($bidData['Bid'])
            && $bid->competitors_bids == $competitorsBids
            && $bid->bid_min_search_price == $this->convertPrice($bidData['MinSearchPrice'])
            && $bid->bid_current_search_price == $this->convertPrice($bidData['CurrentSearchPrice'])
        ) {
            return 0;
        }

        $bid->campaign_id = $bidData['CampaignId'];
        $bid->group_id = $bidData['AdGroupId'];
        $bid->keyword_id = $bidData['KeywordId'];
        $bid->context_bid = $this->convertPrice($bidData['ContextBid']);
        $bid->bid = $this->convertPrice($bidData['Bid']);
        $bid->bid_min_search_price = $this->convertPrice($bidData['MinSearchPrice']);
        $bid->bid_current_search_price = $this->convertPrice($bidData['CurrentSearchPrice']);
        $bid->competitors_bids = $competitorsBids;

        if (!$bid->save()) {
            throw new Exception('Ошибка при сохранении: ' . ArrayHelper::first($bid->getFirstErrors()));
        }

        $this->updateAdSearchPrices($bid, ArrayHelper::getValue($bidData, 'SearchPrices', []));
        $this->updateContextCoverage($bid, ArrayHelper::getValue($bidData, 'ContextCoverage.Items', []));
        $this->updateAuctionBids($bid, ArrayHelper::getValue($bidData, 'AuctionBids', []));

        return 1;
    }

    /**
     * @param YandexBid $bid
     * @param array $auctionBids
     * @throws Exception
     */
    protected function updateAuctionBids(YandexBid $bid, array $auctionBids)
    {
        $bidAuction = $bid->bidAuction;
        if (!$bidAuction) {
            $bidAuction = new AuctionBid(['bid_id' => $bid->primaryKey]);
        }

        foreach ($auctionBids as $auctionBidData) {
            $position = $auctionBidData['Position'];
            $block = mb_substr($position, 1, 1);
            if ($block == 1) {
                $fieldPrefix = 'spec';
            } else {
                $fieldPrefix = 'gar';
            }
            $fieldPrice = $fieldPrefix . mb_substr($position, 2, 1) . '_price';
            $fieldBid = $fieldPrefix . mb_substr($position, 2, 1) . '_bid';
            $bidAuction->{$fieldPrice} = $this->convertPrice($auctionBidData['Price']);
            $bidAuction->{$fieldBid} = $this->convertPrice($auctionBidData['Bid']);
        }
        if (!$bidAuction->save()) {
            throw new Exception('Ошибка при сохранении: ' . ArrayHelper::first($bid->getFirstErrors()));
        }
    }

    /**
     * Обновление доп таблицы
     *
     * @param YandexBid $bid
     * @param array $searchPrices
     * @throws Exception
     */
    protected function updateAdSearchPrices(YandexBid $bid, array $searchPrices)
    {
        $adSearchPrice = $bid->bidAdSearchPrice;
        if (!$adSearchPrice) {
            $adSearchPrice = new AdSearchPrice(['bid_id' => $bid->primaryKey]);
        }

        $fieldMap = [
            'FOOTERBLOCK' => 'footer_block_price',
            'FOOTERFIRST' => 'footer_first_price',
            'PREMIUMBLOCK' => 'premium_block_price',
            'PREMIUMFIRST' => 'premium_first_price'
        ];
        foreach ($searchPrices as $searchPrice) {
            $position = $searchPrice['Position'];
            $fieldName = $fieldMap[$position];
            $adSearchPrice->{$fieldName} = $this->convertPrice($searchPrice['Price']);
        }
        if (!$adSearchPrice->save()) {
            throw new Exception('Ошибка при сохранении: ' . ArrayHelper::first($bid->getFirstErrors()));
        }
    }

    /**
     * @param YandexBid $bid
     * @param array $contextCoverageItems
     * @throws Exception
     */
    protected function updateContextCoverage(YandexBid $bid, array $contextCoverageItems)
    {
        ContextCoverage::deleteAll(['bid_id' => $bid->primaryKey]);

        foreach ($contextCoverageItems as $item) {
            $coverageItem = new ContextCoverage([
                'bid_id' => $bid->id,
                'probability' => $item['Probability'],
                'price' => $this->convertPrice($item['Price']),
            ]);
            if (!$coverageItem->save()) {
                throw new Exception('Ошибка при сохранении: ' . ArrayHelper::first($bid->getFirstErrors()));
            }
        }
    }

    /**
     * @param int $price
     * @return float|int
     */
    protected function convertPrice($price)
    {
        return ($price && $price > 0) ? $price / 1000000 : $price;
    }

    /**
     * @param Account $account
     */
    protected function updateAccount(Account $account)
    {
        $account->last_updated_at = date('Y-m-d H:i:s');
        $account->save();
    }
}
