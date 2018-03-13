<?php

namespace app\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\FileImportLog;

/**
 * FileImportLogSearch represents the model behind the search form about `app\models\FileImportLog`.
 */
class FileImportLogSearch extends FileImportLog
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'file_import_id', 'entity_id'], 'integer'],
            [['title', 'operation', 'old_value', 'new_value', 'entity_type', 'created_at'], 'safe'],
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
        $query = FileImportLog::find();

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
            'file_import_id' => $this->file_import_id,
            'entity_id' => $this->entity_id,
            'created_at' => $this->created_at,
        ]);

        $query->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'operation', $this->operation])
            ->andFilterWhere(['like', 'old_value', $this->old_value])
            ->andFilterWhere(['like', 'new_value', $this->new_value])
            ->andFilterWhere(['like', 'entity_type', $this->entity_type]);

        return $dataProvider;
    }
}
