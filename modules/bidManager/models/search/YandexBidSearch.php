<?php

namespace app\modules\bidManager\models\search;

use app\modules\bidManager\models\YandexAdGroup;
use app\modules\bidManager\models\YandexKeyword;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\bidManager\models\YandexBid;

/**
 * YandexBidSearch represents the model behind the search form about `app\modules\bidManager\models\YandexBid`.
 */
class YandexBidSearch extends YandexBid
{
    /**
     * @var int
     */
    public $strategy_1;

    /**
     * @var int
     */
    public $strategy_2;

    /**
     * @var float
     */
    public $max_click_price;

    /**
     * @var string
     */
    public $group_name;

    /**
     * @var string
     */
    public $keyword;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['keyword', 'group_name'], 'string'],
            [['id', 'campaign_id', 'group_id', 'keyword_id', 'strategy_1', 'strategy_2'], 'integer'],
            [['created_at', 'updated_at', 'bid_serving_status', 'competitors_bids'], 'safe'],
            [['bid', 'context_bid', 'bid_min_search_price', 'bid_current_search_price', 'max_click_price'], 'number'],
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
        $query = YandexBid::find();

        $query->innerJoinWith(['keyword', 'adGroup']);

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
            YandexBid::fullColumn('id') => $this->id,
            YandexBid::fullColumn('campaign_id') => $this->campaign_id,
            YandexBid::fullColumn('group_id') => $this->group_id,
            YandexBid::fullColumn('keyword_id') => $this->keyword_id,
            YandexBid::fullColumn('bid') => $this->bid,
            YandexBid::fullColumn('context_bid') => $this->context_bid,
            YandexBid::fullColumn('bid_min_search_price') => $this->bid_min_search_price,
            YandexBid::fullColumn('bid_current_search_price') => $this->bid_current_search_price,
            YandexKeyword::fullColumn('max_click_price') => $this->max_click_price,
            YandexKeyword::fullColumn('strategy_1') => $this->strategy_1,
            YandexKeyword::fullColumn('strategy_2') => $this->strategy_2,
        ]);

        $query->andFilterWhere(['like', 'bid_serving_status', $this->bid_serving_status])
            ->andFilterWhere(['like', YandexKeyword::fullColumn('keyword'), $this->keyword])
            ->andFilterWhere(['like', YandexAdGroup::fullColumn('name'), $this->group_name])
            ->andFilterWhere(['like', 'competitors_bids', $this->competitors_bids]);

        return $dataProvider;
    }
}
