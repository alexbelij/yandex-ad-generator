<?php

namespace app\modules\bidManager\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\bidManager\models\YandexCampaign;

/**
 * YandexCampaignSearch represents the model behind the search form about `app\modules\bidManager\models\YandexCampaign`.
 */
class YandexCampaignSearch extends YandexCampaign
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'account_id', 'stat_clicks', 'stat_impressions', 'strategy_1', 'strategy_2'], 'integer'],
            [['created_at', 'updated_at', 'title', 'start_date', 'end_date', 'status', 'state', 'status_payment', 'status_clarification', 'currency', 'funds_mode', 'client_info', 'daily_budget_mode'], 'safe'],
            [['funds_sum', 'funds_balance', 'funds_shared_refund', 'funds_shared_spend', 'daily_budget_amount'], 'number'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = YandexCampaign::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'account_id' => $this->account_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'stat_clicks' => $this->stat_clicks,
            'stat_impressions' => $this->stat_impressions,
            'funds_sum' => $this->funds_sum,
            'funds_balance' => $this->funds_balance,
            'funds_shared_refund' => $this->funds_shared_refund,
            'funds_shared_spend' => $this->funds_shared_spend,
            'daily_budget_amount' => $this->daily_budget_amount,
            'strategy_1' => $this->strategy_1,
            'strategy_2' => $this->strategy_2,
        ]);

        $query->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'status', $this->status])
            ->andFilterWhere(['like', 'state', $this->state])
            ->andFilterWhere(['like', 'status_payment', $this->status_payment])
            ->andFilterWhere(['like', 'status_clarification', $this->status_clarification])
            ->andFilterWhere(['like', 'currency', $this->currency])
            ->andFilterWhere(['like', 'funds_mode', $this->funds_mode])
            ->andFilterWhere(['like', 'client_info', $this->client_info])
            ->andFilterWhere(['like', 'daily_budget_mode', $this->daily_budget_mode]);

        return $dataProvider;
    }
}
