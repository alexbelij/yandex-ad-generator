<?php

namespace app\modules\bidManager\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\bidManager\models\YandexKeyword;

/**
 * YandexKeywordSearch represents the model behind the search form about `app\modules\bidManager\models\YandexKeyword`.
 */
class YandexKeywordSearch extends YandexKeyword
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'group_id', 'campaign_id', 'account_id', 'stat_search_clicks', 'stat_search_impressions', 'stat_network_clicks', 'stat_network_impressions'], 'integer'],
            [['keyword', 'created_at', 'updated_at', 'state', 'status'], 'safe'],
            [['bid', 'context_bid'], 'number'],
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
        $query = YandexKeyword::find();

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
            'group_id' => $this->group_id,
            'campaign_id' => $this->campaign_id,
            'account_id' => $this->account_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'bid' => $this->bid,
            'context_bid' => $this->context_bid,
            'stat_search_clicks' => $this->stat_search_clicks,
            'stat_search_impressions' => $this->stat_search_impressions,
            'stat_network_clicks' => $this->stat_network_clicks,
            'stat_network_impressions' => $this->stat_network_impressions,
        ]);

        $query->andFilterWhere(['like', 'keyword', $this->keyword])
            ->andFilterWhere(['like', 'state', $this->state])
            ->andFilterWhere(['like', 'status', $this->status]);

        return $dataProvider;
    }
}
