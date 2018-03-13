<?php

namespace app\modules\bidManager\models\search;

use app\helpers\ArrayHelper;
use app\modules\bidManager\models\Account;
use app\modules\bidManager\models\BidUpdateModel;
use app\modules\bidManager\models\YandexBid;
use yii\base\InvalidParamException;
use yii\base\Model;
use yii\db\Expression;

/**
 * Class BidUpdateSearch
 * @package app\modules\bidManager\models\search
 */
class BidUpdateSearch extends Model
{
    /**
     * @var Account
     */
    public $account;

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            ['account', 'required']
        ];
    }

    /**
     * @param array $params
     * @return \yii\db\ActiveQuery
     */
    public function search($params = [])
    {
        $this->load($params) || $this->load($params, '');

        if (!$this->validate()) {
            throw new InvalidParamException(ArrayHelper::first($this->getErrors()));
        }

        $strategy1Expr = new Expression('
            (CASE
                WHEN bid_yandex_keyword.strategy_1 > 0 THEN bid_yandex_keyword.strategy_1
                WHEN bid_yandex_campaign.strategy_1 > 0 THEN bid_yandex_campaign.strategy_1
                WHEN bid_account.strategy_1 > 0 THEN bid_account.strategy_1
            END) as strategy_1
        ');
        $strategy2Expr = new Expression('
            (CASE
                WHEN bid_yandex_keyword.strategy_2 > 0 THEN bid_yandex_keyword.strategy_2
                WHEN bid_yandex_campaign.strategy_2 > 0 THEN bid_yandex_campaign.strategy_2
                WHEN bid_account.strategy_2 > 0 THEN bid_account.strategy_2
            END) as strategy_2
        ');

        $maxClickPriceExpr = new Expression('
            (CASE
                WHEN bid_yandex_keyword.max_click_price > 0 THEN bid_yandex_keyword.max_click_price
                WHEN bid_yandex_campaign.max_click_price > 0 THEN bid_yandex_campaign.max_click_price
                WHEN bid_account.max_click_price > 0 THEN bid_account.max_click_price
            END) as maxClickPrice
        ');

        $subQuery = YandexBid::find()
            ->select(['bid_yandex_bid.*', $strategy1Expr, $strategy2Expr, $maxClickPriceExpr])
            ->innerJoinWith(['keyword', 'campaign.account'])
            ->andWhere([
                'bid_yandex_keyword.account_id' => $this->account->id
            ]);

        return BidUpdateModel::find()
            ->from(['bid_yandex_bid' => $subQuery])
            ->innerJoinWith(['strategy1', 'strategy2'])
            ->orderBy(['bid_yandex_bid.id' => 'ASC']);
    }
}
