<?php

namespace app\modules\bidManager\lib\services;

use app\lib\ConsoleLogger;
use Psr\Log\LoggerInterface;
use app\lib\api\auth\ApiAccountIdentity;
use app\lib\api\yandex\direct\Connection;
use app\lib\api\yandex\direct\resources\BidResource;
use app\modules\bidManager\lib\bidStrategies\BidStrategyInterface;
use app\modules\bidManager\lib\bidStrategies\GuaranteeStrategy;
use app\modules\bidManager\lib\bidStrategies\SpecStrategy;
use app\modules\bidManager\models\Account;
use app\modules\bidManager\models\BidUpdateModel;
use app\modules\bidManager\models\search\BidUpdateSearch;
use app\modules\bidManager\models\Strategy;
use yii\bootstrap\Html;
use yii\helpers\Url;

/**
 * Class BidUpdater
 * @package app\modules\bidManager\lib
 */
class BidUpdaterService
{
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var BidResource
     */
    protected $bidResource;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var array
     */
    protected $strategies = [
        GuaranteeStrategy::class,
        SpecStrategy::class
    ];

    /**
     * @var array
     */
    protected $strategyInstances = [];

    /**
     * BidUpdaterService constructor.
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger = null)
    {
        $this->connection = new Connection();
        $this->connection->setTimeout(600);
        $this->bidResource = new BidResource($this->connection);

        if (is_null($logger)) {
            $logger = new ConsoleLogger();
        }

        $this->logger = $logger;
    }

    /**
     * Обновление ставок
     *
     * @param Account $account
     * @return int количество обновленных ставок
     */
    public function update(Account $account)
    {
        $this->logger->info('Начинаем обновление ставок');
        $this->connection->setAuthIdentity(new ApiAccountIdentity($account));
        $searchModel = new BidUpdateSearch();
        $query = $searchModel->search(['account' => $account]);
       // $this->logger->info('Выполняем запрос для получения ставок: ' . $query->createCommand()->getRawSql());

        $bidUpdateData = [];

        /** @var BidUpdateModel $bidModel */
        foreach ($query->each() as $bidModel) {
            foreach (['strategy1', 'strategy2'] as $strategyField) {
                $strategyModel = $bidModel->{$strategyField};
                $bidStrategy = $this->getStrategy($strategyModel, $bidModel);
                if ($bidStrategy && ($bidValue = $bidStrategy->getBid($bidModel, $strategyModel))) {
                    $this->logger->info(
                        "Ключевое слово id: {$bidModel->keyword_id}, " .
                        Html::a(
                            $bidModel->keyword->keyword,
                            '/bid-manager/bids?YandexBidSearch[keyword_id]=' . $bidModel->keyword_id
                        ) . ', ставка: ' . $bidValue . ', стратегия: ' . $strategyModel->strategy
                    );
                    $bidUpdateData[] = [
                        'KeywordId' => $bidModel->keyword_id,
                        'Bid' => round($bidValue *  1000000)
                    ];
                    $bidModel->bid = $bidValue;
                    $bidModel->save();
                    break;
                }
            }
        }

        $this->logger->info('Получено ставок для обновления: ' . count($bidUpdateData));

        if (empty($bidUpdateData)) {
            $this->logger->info('Нечего обновлять');
            return 0;
        }

        $count = 0;
        $limit = 9999;
        $offset = 0;
        while ($items = array_slice($bidUpdateData, $offset, $limit)) {
            $offset += $limit;
            //$this->logger->info('Данные для обновления: ' . json_encode($items, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $setResult = $this->bidResource->set($items);
            if (!$setResult->isSuccess()) {
                foreach ($setResult->getErrors() as $error) {
                    $this->logger->error($error->errorInfo());
                }
            }
            $count += $setResult->count();
        }

        $this->logger->info('Обновление завершено');

        return $count;
    }

    /**
     * @param Strategy $strategy
     * @param BidUpdateModel $model
     * @return BidStrategyInterface|null
     */
    protected function getStrategy(Strategy $strategy, BidUpdateModel $model)
    {
        foreach ($this->getStrategies() as $bidStrategy) {
            if ($bidStrategy->isSpecifyBy($model, $strategy)) {
                return $bidStrategy;
            }
        }

        return null;
    }

    /**
     * @return BidStrategyInterface[]
     */
    protected function getStrategies()
    {
        if (empty($this->strategyInstances)) {
            foreach ($this->strategies as $strategy) {
                $this->strategyInstances[$strategy] = new $strategy;
            }
        }

        return $this->strategyInstances;
    }
}
