<?php

namespace app\models;

use app\lib\tasks\TemplateUpdateTask;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "templates".
 *
 * @property integer $id
 * @property integer $shop_id
 * @property string $title
 * @property string $message
 * @property string $updated_at
 * @property int $sort
 * @property int $price_from
 * @property int $price_to
 *
 * @property CampaignTemplate[] $campaignTemplates
 * @property Shop $shop
 * @property ExternalCategory[] $categories
 * @property ExternalBrand[] $brands
 * @property AdTemplateBrand[] $templateBrands
 * @property AdTemplateCategory[] $templateCategories
 */
class AdTemplate extends BaseModel
{
    const TITLE_MAX_SIZE = 33;

    /**
     * @var array
     */
    private $campaignTemplateIds = [];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ad_template';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['shop_id', 'title', 'message', 'price_from', 'price_to'], 'required'],
            [['shop_id', 'sort', 'price_from', 'price_to'], 'integer'],
            [['title', 'message'], 'string', 'max' => 150],
            [['updated_at', 'campaignTemplateIds'], 'safe'],
            ['campaignTemplateIds', 'required', 'isEmpty' => function ($val) {
                return empty($val);
            }]
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Id',
            'shop_id' => 'Магазин',
            'title' => 'Заголовок',
            'message' => 'Объявление',
            'campaignTemplateIds' => 'Шаблоны кампании',
            'sort' => 'Порядок сортировки',
            'price_from' => 'Цена с',
            'price_to' => 'Цена по'
        ];
    }

    /**
     * @inheritDoc
     */
    public function beforeSave($insert)
    {
        if ($insert) {
            $this->updated_at = date('Y-m-d H:i:s');
        } else {
            if ($this->getOldAttribute('title') != $this->title ||
                $this->getOldAttribute('message') != $this->message ||
                $this->getOldAttribute('sort') != $this->sort
            ) {
                $this->updated_at = date('Y-m-d H:i:s');
            }
        }

        return parent::beforeSave($insert);
    }

    /**
     * @inheritDoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        $existsCampaignTemplateIds = ArrayHelper::getColumn($this->campaignTemplates, 'id');
        $toRemoveCampaigns = array_diff($existsCampaignTemplateIds, $this->campaignTemplateIds);
        $toAddCampaigns = array_diff($this->campaignTemplateIds, $existsCampaignTemplateIds);

        if (!empty($toRemoveCampaigns)) {
            foreach ($this->campaignTemplates as $campaignTemplate) {
                if (in_array($campaignTemplate->primaryKey, $toRemoveCampaigns)) {
                    $this->unlink('campaignTemplates', $campaignTemplate);
                }
            }
        }

        foreach ($toAddCampaigns as $campaignTemplateId) {
            $campaignTemplate = CampaignTemplate::findOne($campaignTemplateId);
            $this->link('campaignTemplates', $campaignTemplate);
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCampaignTemplates()
    {
        return $this->hasMany(CampaignTemplate::className(), ['id' => 'campaign_template_id'])
            ->viaTable('ad_template_campaign_template', ['ad_template_id' => 'id']);
    }

    /**
     * @return array
     */
    public function getCampaignTemplateIds()
    {
        if (empty($this->campaignTemplateIds)) {
            $this->campaignTemplateIds = ArrayHelper::getColumn($this->campaignTemplates, 'id');
        }
        return $this->campaignTemplateIds;
    }

    /**
     * @param array $campaignTemplateIds
     */
    public function setCampaignTemplateIds($campaignTemplateIds)
    {
        $this->campaignTemplateIds = $campaignTemplateIds;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShop()
    {
        return $this->hasOne(Shop::className(), ['id' => 'shop_id']);
    }

    /**
     * @return array
     */
    public function getBrandIds()
    {
        return ArrayHelper::getColumn($this->templateBrands, 'brand_id');
    }

    /**
     * @return array
     */
    public function getCategoryIds()
    {
        return ArrayHelper::getColumn($this->templateCategories, 'category_id');
    }

    /**
     * @return ActiveQuery
     */
    public function getTemplateBrands()
    {
        return $this->hasMany(AdTemplateBrand::className(), ['ad_template_id' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getTemplateCategories()
    {
        return $this->hasMany(AdTemplateCategory::className(), ['ad_template_id' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getBrands()
    {
        return $this->hasMany(ExternalBrand::className(), ['id' => 'brand_id'])
            ->viaTable('ad_template_brand', ['ad_template_id' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getCategories()
    {
        return $this->hasMany(ExternalCategory::className(), ['id' => 'category_id'])
            ->viaTable('ad_template_category', ['ad_template_id' => 'id']);
    }
}
