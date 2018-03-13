<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 10.04.16
 * Time: 15:41
 */

namespace app\models\search;

use app\models\YandexUpdateLog;
use yii\base\Model;
use yii\data\ActiveDataProvider;

class UpdateLogSearch extends YandexUpdateLog
{
    /**
     * @var string
     */
    public $entityTitle;

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            [['entity_type', 'entity_id', 'operation', 'status', 'created_at', 'entityTitle'], 'safe']
        ];
    }

    /**
     * @inheritDoc
     */
    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params = [])
    {
        $query = YandexUpdateLog::find();

        //$query->joinWith(['product', 'campaign']);

        $query->leftJoin('ad_yandex_campaign', 'ad_yandex_campaign.id = yandex_update_log.entity_id AND yandex_update_log.entity_type=\'YandexAd\'');
        $query->leftJoin('ad', 'ad_yandex_campaign.ad_id = ad.id');
        $query->leftJoin('yandex_campaign', 'yandex_campaign.id = yandex_update_log.entity_id AND yandex_update_log.entity_type=\'campaign\'');
        
        $dataProvider = new ActiveDataProvider([
            'query' => $query
        ]);
        
        $this->load($params);

        $query->andWhere(['yandex_update_log.task_id' => $this->task_id]);
        
        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'entity_id' => $this->entity_id,
            'created_at' => $this->created_at
        ]);

        $query->andFilterWhere(['LIKE', 'yandex_update_log.entity_type', $this->entity_type]);
        $query->andFilterWhere(['LIKE', 'yandex_update_log.operation', $this->operation]);
        $query->andFilterWhere(['LIKE', 'yandex_update_log.status', $this->status]);

        $query->andFilterWhere(['OR',
            ['LIKE', 'ad.title', $this->entityTitle],
            ['LIKE', 'yandex_campaign.title', $this->entityTitle]
        ]);
        
        return $dataProvider;
    }
}