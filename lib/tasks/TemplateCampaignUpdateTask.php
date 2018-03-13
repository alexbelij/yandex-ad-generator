<?php
/**
 * Project Golden Casino.
 */

namespace app\lib\tasks;

use app\lib\api\auth\ApiAccountIdentity;
use app\lib\api\yandex\direct\exceptions\ConnectionException;
use app\lib\api\yandex\direct\query\ResultItem;
use app\lib\api\yandex\direct\resources\AdGroupResource;
use app\lib\api\yandex\direct\resources\CampaignResource;
use app\lib\services\AdGroupService;
use app\lib\services\YandexCampaignService;
use app\models\Account;
use app\models\AdYandexCampaign;
use app\models\CampaignTemplate;
use app\models\YandexCampaign;
use app\models\YandexUpdateLog;
use yii\base\Exception;
use app\helpers\ArrayHelper;

/**
 * Class TemplateCampaignUpdateTask
 * @package app\lib\tasks
 * @author Denis Dyadyun <sysadm85@gmail.com>
 */
class TemplateCampaignUpdateTask extends YandexBaseTask
{
    const TASK_NAME = 'TemplateCampaignUpdate';

    /**
     * @var YandexCampaignService
     */
    protected $campaignService;

    /**
     * @var AdGroupService
     */
    protected $adGroupService;

    /**
     * @inheritDoc
     */
    protected function init()
    {
        parent::init();
        $this->campaignService = new YandexCampaignService(new CampaignResource($this->connection));
        $this->adGroupService = new AdGroupService(new AdGroupResource($this->connection));
    }


    /**
     * @inheritDoc
     */
    public function execute($params = [])
    {
        $context = $this->task->getContext();
        $campaignId = $context['template_id'];

        $campaignTemplate = CampaignTemplate::findOne($campaignId);

        if (!$campaignTemplate) {
            throw new Exception('Шаблон кампании не найден');
        }

        if (!empty($context['region_are_change'])) {
            $this->updateRegions($campaignTemplate);
        }

        $this->updateCampaigns($campaignTemplate);
    }

    /**
     * @param CampaignTemplate $campaignTemplate
     * @throws ConnectionException
     */
    protected function updateCampaigns(CampaignTemplate $campaignTemplate)
    {
        /** @var YandexCampaign[] $campaigns */
        $query = YandexCampaign::find()
            ->andWhere(['campaign_template_id' => $campaignTemplate->primaryKey])
            ->andWhere('yandex_id is not null')
            ->indexBy('yandex_id');

        foreach ($query->batch(10) as $campaigns) {
            $result = [];

            foreach (ArrayHelper::groupBy($campaigns, 'account_id') as $accountId => $accountCampaigns) {
                $account =  Account::findOne($accountId);

                if (!$account) {
                    continue;
                }

                if (empty($accountCampaigns)) {
                    continue;
                }

                $this->connection->setAuthIdentity(new ApiAccountIdentity($account));

                try {
                    $updateResult = $this->campaignService->updateCampaign(
                        ArrayHelper::getColumn($accountCampaigns, 'yandex_id')
                    );
                    $result = array_merge($result, $updateResult->getResult());
                } catch (ConnectionException $e) {
                    if ($e->getCode() == ConnectionException::TOKEN_NOT_FOUND) {
                        YandexCampaign::deleteAll(['id' => ArrayHelper::getColumn($accountCampaigns, 'id')]);
                    } else {
                        throw $e;
                    }
                }

            }

            if (!$result) {
                return;
            }

            /** @var ResultItem $resultItem */
            foreach ($result as $resultItem) {
                if (!isset($campaigns[$resultItem->getId()])) {
                    continue;
                }
                $yandexCampaign = $campaigns[$resultItem->getId()];

                $message = 'Кампания обновлена';
                if (!$resultItem->isOk()) {
                    if ($resultItem->hasError()) {
                        $message = $resultItem->firstError()->errorInfo();
                    } elseif ($resultItem->getWarnings()) {
                        $message = $resultItem->firstWarning()->errorInfo();
                    } else {
                        $message = 'Неизвестная ошибка';
                    }
                }

                $this->logOperation(
                    $yandexCampaign,
                    'updateCampaignTemplate',
                    $resultItem->isOk() ? YandexUpdateLog::STATUS_SUCCESS : YandexUpdateLog::STATUS_ERROR,
                    $message
                );
            }
        }
    }

    /**
     * @param CampaignTemplate $campaignTemplate
     * @throws ConnectionException
     */
    protected function updateRegions(CampaignTemplate $campaignTemplate)
    {
        $yandexQuery = AdYandexCampaign::find()
            ->select('ayc.*')
            ->from(['ayc' => 'ad_yandex_campaign'])
            ->innerJoin(['yc' => 'yandex_campaign'], 'yc.id = ayc.yandex_campaign_id')
            ->andWhere(['campaign_template_id' => $campaignTemplate->primaryKey])
            ->andWhere('yandex_adgroup_id IS NOT NULL')
            ->indexBy('yandex_adgroup_id');

        foreach ($yandexQuery->batch(950) as $yandexAds) {
            $result = [];
            foreach (ArrayHelper::groupBy($yandexAds, 'account_id') as $accountId => $accountYandexAds) {
                $account = Account::findOne($accountId);
                if (!$account) {
                    continue;
                }
                $this->connection->setAuthIdentity(new ApiAccountIdentity($account));
                $yandexAdGroupIds = array_filter(ArrayHelper::getColumn($yandexAds, 'yandex_adgroup_id'));
                if (empty($yandexAdGroupIds)) {
                    continue;
                }
                try {
                    $updateResult = $this->adGroupService->update($yandexAdGroupIds, $campaignTemplate);
                    $result = array_merge($result, $updateResult->getResult());
                } catch (ConnectionException $e) {
                    if ($e->getCode() != ConnectionException::TOKEN_NOT_FOUND) {
                        throw $e;
                    }
                }
            }

            foreach ($result as $resultItem) {
                if (!isset($yandexAds[$resultItem->getId()])) {
                    continue;
                }
                $yandexAd = $yandexAds[$resultItem->getId()];
                $this->logOperation(
                    $yandexAd,
                    'updateRegion',
                    $resultItem->isOk() ? YandexUpdateLog::STATUS_SUCCESS : YandexUpdateLog::STATUS_ERROR,
                    $resultItem->isOk() ? 'Обновление региона' : (string)$resultItem->firstError()
                );
            }
        }
    }
}
