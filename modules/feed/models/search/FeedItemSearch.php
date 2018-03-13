<?php

namespace app\modules\feed\models\search;

use app\helpers\ArrayHelper;
use app\helpers\TreeHelper;
use app\modules\feed\models\FeedBrand;
use app\modules\feed\models\FeedCategory;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\feed\models\FeedItem;

/**
 * FeedItemSearch represents the model behind the search form about `app\modules\feed\models\FeedItem`.
 */
class FeedItemSearch extends FeedItem
{
    /**
     * @var int
     */
    public $priceFrom;

    /**
     * @var int
     */
    public $priceTo;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'feed_queue_id', 'brand_id', 'category_id', 'is_active', 'feed_id'], 'safe'],
            [['item_text', 'name'], 'string'],
            [['priceFrom', 'priceTo'], 'integer'],
        ];
    }

    /**
     * @inheritDoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'priceFrom' => 'Цена от',
            'priceTo' => 'Цена по'
        ]);
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
    public function search($params = [])
    {
        $query = FeedItem::find();

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
            'feed_queue_id' => $this->feed_queue_id,
            'brand_id' => $this->brand_id,
            'category_id' => $this->category_id,
            'is_active' => $this->is_active,
            'feed_id' => $this->feed_id,
        ]);

        if ($this->priceFrom) {
            $query->andWhere(['>=', 'price', $this->priceFrom]);
        }

        if ($this->priceTo) {
            $query->andWhere(['<=', 'price', $this->priceTo]);
        }

        $query
            ->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'item_text', $this->item_text]);

        return $dataProvider;
    }

    /**
     * @return array
     */
    public function getBrands()
    {
        return FeedBrand::find()
            ->select('id, title')
            ->andWhere(['feed_id' => $this->feed_id])
            ->asArray()
            ->all();
    }

    /**
     * @return FeedCategory[]|array|\yii\db\ActiveRecord[]
     */
    public function getCategoriesList()
    {
        return FeedCategory::find()
            ->andWhere(['feed_id' => $this->feed_id])
            ->asArray()
            ->all();
    }

    /**
     * @return array
     */
    public function getCategories()
    {
        $allCategories = FeedCategory::find()
            ->andWhere(['feed_id' => $this->feed_id])
            ->asArray()
            ->all();

        return TreeHelper::getCategoriesTree($allCategories, (array)$this->category_id, true);
    }
}
