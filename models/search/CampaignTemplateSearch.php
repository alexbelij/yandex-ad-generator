<?php

namespace app\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\CampaignTemplate;

/**
 * CampaignTemplateSearch represents the model behind the search form about `app\models\CampaignTemplate`.
 */
class CampaignTemplateSearch extends CampaignTemplate
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'shop_id'], 'integer'],
            [['regions', 'negative_keywords', 'text_campaign'], 'safe'],
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
        $query = CampaignTemplate::find();

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
            'shop_id' => $this->shop_id,
        ]);

        $query->andFilterWhere(['like', 'regions', $this->regions])
            ->andFilterWhere(['like', 'negative_keywords', $this->negative_keywords])
            ->andFilterWhere(['like', 'text_campaign', $this->text_campaign]);

        return $dataProvider;
    }
}
