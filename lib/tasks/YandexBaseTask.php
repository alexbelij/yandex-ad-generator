<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 28.04.16
 * Time: 22:18
 */

namespace app\lib\tasks;

use app\components\FileLogger;
use app\lib\api\auth\ApiAccountIdentity;
use app\lib\api\auth\ApiIdentityInterface;
use app\lib\api\shop\models\ExtProduct;
use app\lib\api\yandex\auth\BrandApiIdentity;
use app\lib\api\yandex\direct\Connection;
use app\lib\api\yandex\direct\events\UnitUpdateListener;
use app\lib\LoggedInterface;
use app\lib\PointsCalculator;
use app\lib\YandexOperationLogger;
use app\models\Account;
use app\models\BrandAccount;
use app\models\Shop;
use app\models\YandexUpdateLog;
use yii\base\Exception;

/**
 * Class YandexBaseTask
 * @package app\lib\tasks
 * @author Denis Dyadyun <sysadm85@gmail.com>
 */
abstract class YandexBaseTask extends AbstractTask
{
    /**
     * @var FileLogger
     */
    protected $logger;

    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var Shop
     */
    protected $shop;

    /**
     * @var YandexOperationLogger
     */
    protected $yandexOperationLogger;

    /**
     * @var PointsCalculator
     */
    protected $pointsCalculator;

    /**
     * @inheritdoc
     */
    protected function init()
    {
        parent::init();
        $this->logger = $this->getLogger();
        /** @var Shop $shop */
        $this->shop = Shop::findOne($this->task->shop_id);

        $this->connection = new Connection(new BrandApiIdentity($this->shop));
        $this->connection->setTimeout(600);
        $unitUpdateListener = new UnitUpdateListener();
        $this->pointsCalculator = PointsCalculator::getInstance();
        $this->yandexOperationLogger = new YandexOperationLogger($this->task);
        $this->yandexOperationLogger->setPointsCalculator($this->pointsCalculator);

        $this->connection->on(Connection::EVENT_AFTER_REQUEST, [$unitUpdateListener, 'update']);
    }

    /**
     * @param PointsCalculator $pointsCalculator
     */
    public function setPointsCalculator($pointsCalculator)
    {
        $this->pointsCalculator = $pointsCalculator;
    }

    /**
     * Логирование операции
     * 
     * @param LoggedInterface $model
     * @param $operation
     * @param string $status
     * @param string $message
     */
    protected function logOperation(
        LoggedInterface $model, $operation, $status = 'success', $message = ''
    ) {
        $this->yandexOperationLogger->log($model, $operation, $status, $message);
        $consoleLogMessage = "$status $operation " . $model->getEntityType() . ' ' . $message;
        $this->logger->log($consoleLogMessage);
    }

    /**
     * Возвращает токен для выполнения запросов к апи директа
     *
     * @param Shop $shop
     * @param int $brandId
     * @return BrandApiIdentity
     */
    protected function getYandexToken(Shop $shop, $brandId)
    {
        static $cache = [];

        if (!array_key_exists($brandId, $cache)) {
            $cache[$brandId] = new BrandApiIdentity($shop, $brandId);
        }

        return $cache[$brandId];
    }

    /**
     * @param int $accountId
     * @return Account
     */
    protected function getAccount($accountId)
    {
        static $cache = [];
        if (!array_key_exists($accountId, $cache)) {
            $cache[$accountId] = Account::findOne($accountId);
        }

        return $cache[$accountId];
    }

    /**
     * Установить токен аккаунта для выполнения запросов к апи директа
     *
     * @param int|Account $account
     * @return ApiIdentityInterface
     * @throws Exception
     */
    protected function setAccountToken($account)
    {
        if (!($account instanceof Account)) {
            $account = $this->getAccount($account);
        }

        if (!$account) {
            throw new Exception('Account not found');
        }

        $authIdentity = new ApiAccountIdentity($account);
        $this->connection->setAuthIdentity($authIdentity);

        return $authIdentity;
    }

    /**
     * @inheritDoc
     */
    protected function getLogFileName()
    {
        return 'task_queue_log_' . $this->task->id;
    }
}
