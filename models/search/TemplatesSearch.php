<?php

namespace app\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\AdTemplate;

/**
 * TemplatesSearch represents the model behind the search form about `app\models\Template`.
 */
class TemplatesSearch extends AdTemplate
{
    /**
     * @var mixed
     */
    public $brandId;

    /**
     * @var mixed
     */
    public $categoryId;

    /**
     * @var int
     */
    public $price;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'shop_id'], 'integer'],
            [['title', 'message', 'price_from', 'price_to', 'price', 'brandId', 'categoryId'], 'safe'],
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
        $query = AdTemplate::find()
            ->distinct()
            ->leftJoin('ad_template_category atc', 'atc.ad_template_id = ad_template.id')
            ->leftJoin('ad_template_brand atb', 'atb.ad_template_id = ad_template.id');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params) || $this->load($params, '');

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'shop_id' => $this->shop_id
        ]);

        if (!empty($this->categoryId) && !empty($this->brandId)) {
            $query->andWhere(['OR', ['atc.category_id' => $this->categoryId], ['atb.brand_id' => $this->brandId]]);
        } else {
            $query->andFilterWhere([
                'atc.category_id' => $this->categoryId,
                'atb.brand_id' => $this->brandId
            ]);
        }

        if ($this->price) {
            $query
                ->andWhere(['<=', 'price_from', $this->price])
                ->andWhere(['>=', 'price_to', $this->price]);
        }

        $query->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'message', $this->message]);

        return $dataProvider;
    }
}
