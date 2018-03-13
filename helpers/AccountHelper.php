<?php

namespace app\helpers;

use app\models\Account;
use app\models\BrandAccount;
use app\models\ExternalBrand;
use app\models\search\ProductsSearch;
use app\models\Shop;
use app\models\YandexCampaign;

/**
 * Class AccountHelper
 * @package app\helpers
 */
class AccountHelper
{
    /**
     * Возвращает список id аккаунтов, которые учавствуют в обновлении магазина
     * и переданных брендов
     *
     * @param int[] $brandIds
     * @param Shop $shop
     * @return int[]
     */
    public static function getAccountIds(Shop $shop, $brandIds = [])
    {
        $accountIds = [];
        if (empty($brandIds)) {
            $accountIds[] = $shop->account_id;
        }

        $brandAccountIds = BrandAccount::find()
            ->select('account_id')
            ->distinct()
            ->andFilterWhere([
                'brand_id' => $brandIds ?: null,
                'shop_id' => $shop->id
            ])
            ->column();

        if (!empty($brandAccountIds)) {
            $accountIds = array_merge($accountIds, $brandAccountIds);
            $accountIds = array_values(array_unique($accountIds));

            return $accountIds;
        } else {
            return [$shop->account_id];
        }
    }

    /**
     * Возвращает список брендов проиндектированных по ид аккаунта
     *
     * @param Shop $shop
     * @param null|int[] $brandIds
     * @param null|int[] $accountIds
     * @return array
     */
    public static function getBrandsByAccount(Shop $shop, $brandIds = null, $accountIds = null)
    {
        $accountIds = $accountIds ?: self::getAccountIds($shop);

        $joinField = ProductsSearch::getProductJoinField($shop);

        /** @var ExternalBrand[] $brands */
        $brands = ExternalBrand::find()
            ->andWhere(['shop_id' => $shop->id])
            ->andFilterWhere([$joinField => $brandIds])
            ->indexBy($joinField)
            ->all();

        $brandAccounts = BrandAccount::find()
            ->select('account_id')
            ->andFilterWhere([
                'shop_id' => $shop->id,
                'account_id' => $accountIds
            ])
            ->indexBy('brand_id')
            ->column();


        $result = [];
        foreach ($brands as $brand) {
            $accountId = $shop->account_id;
            $brandId = $brand->$joinField;
            if (isset($brandAccounts[$brandId])) {
                $accountId = $brandAccounts[$brandId];
            }
            $result[$accountId][] = [
                'id' => $brandId,
                'title' => $brand->title
            ];
        }

        return $result;
    }

    /**
     * @param array $yaCampaignIds
     * @return array
     */
    public static function groupCampaignsByAccountIds(array $yaCampaignIds)
    {
        $accounts = [];
        /** @var Account[] $accountsCampaigns */
        $accountsCampaigns = [];
        /** @var YandexCampaign[] $campaigns */
        $campaigns = YandexCampaign::find()
            ->andWhere(['yandex_id' => $yaCampaignIds])
            ->indexBy('yandex_id')
            ->all();

        foreach ($campaigns as $yaCampaign) {

            $account = $yaCampaign->account;

            if (!$account) {
                continue;
            }

            if (!isset($accounts[$account->id])) {
                $accounts[$account->id] = $account;
            }
            $accountsCampaigns[$account->id][] = $yaCampaign->yandex_id;
        }

        return [$accounts, $accountsCampaigns];
    }
}
