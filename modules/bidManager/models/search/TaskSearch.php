<?php

namespace app\modules\bidManager\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\bidManager\models\Task;

/**
 * TaskSearch represents the model behind the search form about `app\modules\bidManager\models\Task`.
 */
class TaskSearch extends Task
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'account_id'], 'integer'],
            [['created_at', 'started_at', 'finished_at', 'status', 'task', 'context', 'message'], 'safe'],
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
        $query = Task::find();

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
            'created_at' => $this->created_at,
            'started_at' => $this->started_at,
            'finished_at' => $this->finished_at,
            'account_id' => $this->account_id,
        ]);

        $query->andFilterWhere(['like', 'status', $this->status])
            ->andFilterWhere(['like', 'task', $this->task])
            ->andFilterWhere(['like', 'context', $this->context])
            ->andFilterWhere(['like', 'message', $this->message]);

        return $dataProvider;
    }
}
