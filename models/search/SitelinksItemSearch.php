<?php

namespace app\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\SitelinksItem;

/**
 * SitelinksItemSearch represents the model behind the search form about `app\models\SitelinksItem`.
 */
class SitelinksItemSearch extends SitelinksItem
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'sitelink_id'], 'integer'],
            [['title', 'href', 'description'], 'safe'],
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
        $query = SitelinksItem::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'sitelink_id' => $this->sitelink_id,
        ]);

        $query->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'href', $this->href])
            ->andFilterWhere(['like', 'description', $this->description]);

        return $dataProvider;
    }
}
