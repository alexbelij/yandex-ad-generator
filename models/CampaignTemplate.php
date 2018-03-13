<?php

namespace app\models;

use app\lib\tasks\TemplateCampaignUpdateTask;
use app\models\campaignTemplate\TextCampaign;
use app\models\query\CampaignTemplateQuery;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "campaign_template".
 *
 * @property integer $id
 * @property string $title
 * @property integer $shop_id
 * @property string $regions
 * @property string $negative_keywords
 * @property string $text_campaign
 *
 * @property Shop $shop
 * @property TextCampaign $textCampaign
 * @property CampaignTemplateBrand[] $campaignTemplateBrands
 * @property YandexCampaign[] $yandexCampaigns
 */
class CampaignTemplate extends BaseModel
{
    /**
     * @var TextCampaign
     */
    private $textCampaign;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'campaign_template';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['shop_id'], 'integer'],
            [['title'], 'required'],
            [['negative_keywords', 'text_campaign', 'title'], 'string'],
            [['regions'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Название шаблона',
            'shop_id' => 'Магазин',
            'regions' => 'Регионы показа',
            'negative_keywords' => 'Минус слова',
            'text_campaign' => 'Text Campaign',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShop()
    {
        return $this->hasOne(Shop::className(), ['id' => 'shop_id']);
    }

    /**
     * @inheritdoc
     * @return CampaignTemplateQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new CampaignTemplateQuery(get_called_class());
    }

    /**
     * @inheritDoc
     */
    public function afterFind()
    {
        parent::afterFind();
        $this->initModelFields();
    }

    /**
     * @inheritDoc
     */
    public function init()
    {
        parent::init();
        $this->initModelFields();
    }

    /**
     * Инициализация json полей
     */
    protected function initModelFields()
    {
        $textCampaignData = json_decode($this->text_campaign, true);
        if ($textCampaignData) {
            $this->textCampaign = new TextCampaign($textCampaignData);
        } else {
            $this->textCampaign = new TextCampaign();
        }
    }

    /**
     * @inheritDoc
     */
    public function beforeSave($insert)
    {
        $this->text_campaign = json_encode($this->textCampaign->toArray());
        return parent::beforeSave($insert);
    }

    /**
     * @inheritDoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        unset($changedAttributes['id']);
        if (!empty($changedAttributes) && !$insert) {
            $context = ['template_id' => $this->primaryKey];
            if (!empty($changedAttributes['regions'])) {
                $context['region_are_change'] = 1;
            }

            if (!TaskQueue::hasActiveTasks($this->shop_id, TemplateCampaignUpdateTask::TASK_NAME, $context)) {
                TaskQueue::createNewTask($this->shop_id, TemplateCampaignUpdateTask::TASK_NAME, $context);
            }
        }
    }

    /**
     * @return TextCampaign
     */
    public function getTextCampaign()
    {
        return $this->textCampaign;
    }

    /**
     * @param TextCampaign $textCampaign
     */
    public function setTextCampaign(TextCampaign $textCampaign)
    {
        $this->textCampaign = $textCampaign;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCampaignTemplateBrands()
    {
        return $this->hasMany(CampaignTemplateBrand::className(), ['campaign_template_id' => 'id']);
    }

    /**
     * Применяется ли шаблон к переданному бренду
     *
     * @param int $brandId
     * @return bool
     */
    public function isAllowForBrand($brandId)
    {
        $brandIds = ArrayHelper::getColumn($this->campaignTemplateBrands, 'brand_id');

        if (empty($brandIds) || in_array(0, $brandIds)) {
            return in_array($brandId, $this->getGeneratorSettingsBrandIds());
        } else {
            return in_array($brandId, $brandIds);
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getYandexCampaigns()
    {
        return $this->hasMany(YandexCampaign::className(), ['campaign_template_id' => 'id']);
    }

    /**
     * @return int[]
     */
    protected function getGeneratorSettingsBrandIds()
    {
        static $brandIds = [];

        if (!array_key_exists($this->shop_id, $brandIds)) {
            /** @var GeneratorSettings $generatorSettings */
            $generatorSettings = GeneratorSettings::find()
                ->andWhere(['shop_id' => $this->shop_id])
                ->one();

            if ($generatorSettings && $generatorSettings->brands) {
                $brandIds[$this->shop_id] = array_map('intval', explode(',', $generatorSettings->brands));
            } else {
                $brandIds[$this->shop_id] = [];
            }
        }

        return $brandIds[$this->shop_id];
    }
}
