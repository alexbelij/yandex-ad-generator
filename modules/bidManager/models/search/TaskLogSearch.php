<?php

namespace app\modules\bidManager\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\modules\bidManager\models\TaskLog;

/**
 * TaskLogSearch represents the model behind the search form about `app\modules\bidManager\models\TaskLog`.
 */
class TaskLogSearch extends TaskLog
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'task_id'], 'integer'],
            [['created_at', 'level', 'message', 'context'], 'safe'],
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
        $query = TaskLog::find();

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
            'task_id' => $this->task_id,
            'created_at' => $this->created_at,
        ]);

        $query->andFilterWhere(['like', 'level', $this->level])
            ->andFilterWhere(['like', 'message', $this->message])
            ->andFilterWhere(['like', 'context', $this->context]);

        return $dataProvider;
    }
}
