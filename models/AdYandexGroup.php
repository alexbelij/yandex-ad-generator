<?php

namespace app\models;

use app\lib\LoggedInterface;
use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%ad_yandex_group}}".
 *
 * @property integer $id
 * @property string $yandex_adgroup_id
 * @property integer $keywords_count
 * @property integer $ads_count
 * @property string $status
 * @property string $serving_status
 * @property integer $yandex_campaign_id
 * @property AdYandexCampaign[] $yandexAds
 * @property YandexCampaign $yandexCampaign
 */
class AdYandexGroup extends BaseModel implements LoggedInterface
{
    const STATUS_DRAFT = 'draft';
    const STATUS_MODERATION = 'moderation';
    const STATUS_PREACCEPTED = 'preaccepted';
    const STATUS_ACCEPTED = 'accepted';

    const SERVING_STATUS_ELIGIBLE = 'eligible';
    const SERVING_STATUS_RARELY_SERVED = 'rarely_served';

    const MAX_ADS = 50;
    const MAX_KEYWORDS = 200;

    public static $goodStatuses = [
        self::STATUS_DRAFT,
        self::STATUS_MODERATION,
        self::STATUS_ACCEPTED,
        self::STATUS_PREACCEPTED,
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ad_yandex_group}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['yandex_adgroup_id'], 'required'],
            [['yandex_adgroup_id'], 'safe'],
            [['keywords_count', 'ads_count', 'yandex_campaign_id'], 'integer'],
            [['status', 'serving_status'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Id',
            'yandex_adgroup_id' => 'ID of group in the Yandex',
            'keywords_count' => 'Total count of keywords of ads in the group',
            'status' => 'Status',
            'serving_status' => 'Serving status',
            'ads_count' => 'Ads count',
            'yandex_campaign_id' => 'Yandex Campaign ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getYandexCampaign()
    {
        return $this->hasOne(YandexCampaign::className(), ['id' => 'yandex_campaign_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getYandexAds()
    {
        return $this->hasMany(AdYandexCampaign::className(), ['ad_yandex_group_id' => 'id']);
    }

    /**
     * @return string[]
     */
    public function getKeywords()
    {
        $result = [];
        foreach ($this->yandexAds as $yandexAd) {
            $keywords = $yandexAd->ad->getKeywordsList();
            $keywords = array_map('trim', $keywords);
            $result = array_merge($result, $keywords);
        }

        return array_filter(array_unique($result));
    }

    /**
     * @return AdKeyword[]
     */
    public function getKeywordsModels()
    {
        $result = [];
        foreach ($this->yandexAds as $yandexAd) {
            $keywords = $yandexAd->ad->adKeywords;
            foreach ($keywords as $keyword) {
                $keyword->keyword = trim($keyword->keyword);
                if ($keyword->keyword) {
                    $result[] = $keyword;
                }
            }
        }

        return $result;
    }

    /**
     * @param AdYandexCampaign $yandexAd
     * @param int $keywordsCount
     * @return AdYandexGroup|array|ActiveRecord
     */
    public static function findSuitable($yandexAd, $keywordsCount = null)
    {
        if (is_null($keywordsCount)) {
            $keywordsCount = $yandexAd->ad->getAdKeywords()->count();
        }

        return AdYandexGroup::find()
            ->andWhere([
                'yandex_campaign_id' => $yandexAd->yandex_campaign_id,
                'serving_status' => AdYandexGroup::SERVING_STATUS_ELIGIBLE,
                'status' => AdYandexGroup::$goodStatuses,
            ])
            ->andWhere(['!=', 'id', $yandexAd->ad_yandex_group_id])
            ->andWhere([
                '<=',
                'ads_count',
                AdYandexGroup::MAX_ADS - 1
            ])
            ->andWhere([
                '<=',
                'keywords_count',
                AdYandexGroup::MAX_KEYWORDS - $keywordsCount
            ])
            ->orderBy(['id' => SORT_ASC])
            ->one();

    }

    public function updateAdsData()
    {
        $adsCount = 0;
        $keywordsCount = 0;

        foreach ($this->getYandexAds()->joinWith(['ad.adKeywords'])->all() as $yandexAd) {
            $adsCount++;
            $keywordsCount += $yandexAd->ad->getAdKeywords()->count();
        }

        $this->ads_count = $adsCount;
        $this->keywords_count = $keywordsCount;

        $this->save();
    }

    public function getEntityType()
    {
        return 'group';
    }

    public function getEntityId()
    {
        return $this->primaryKey;
    }
}
