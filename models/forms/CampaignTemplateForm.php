<?php

namespace app\models\forms;

use app\lib\api\shop\gateways\BrandsGateway;
use app\models\CampaignTemplate;
use app\models\CampaignTemplateBrand;
use app\models\GeneratorSettings;
use app\models\Shop;
use yii\helpers\ArrayHelper;

/**
 * Class CampaignTemplateForm
 * @package app\models\forms
 * @author Denis Dyadyun <sysadm85@gmail.com>
 */
class CampaignTemplateForm extends CampaignTemplate
{
    /**
     * @var array
     */
    private $regionList;

    /**
     * @var array
     */
    private $minusRegionList;

    /**
     * @var int[]
     */
    private $brandIds;

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [
                ['regionList', 'brandIds'],
                'required',
                'isEmpty' => function ($val) {
                    return empty($val);
                }
            ],
            [['minusRegionList'], 'safe']
        ]);
    }

    /**
     * @inheritDoc
     */
    public function load($data, $formName = null)
    {
        return parent::load($data, $formName) &&
        $this->getTextCampaign()->load($data, $formName);
    }

    /**
     * @inheritDoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'regionList' => 'Список регионов показа',
            'minusRegionList' => 'Регионы, где показы будут отключены',
            'brandIds' => 'Бренды, для которых будет применен шаблон'
        ]);
    }

    /**
     * @return array
     */
    public function getRegionList()
    {
        if (is_null($this->regionList)) {
            $this->regionList = array_filter(explode(',', $this->regions), function ($region) {
                return $region >= 0;
            });
        }

        return $this->regionList;
    }

    /**
     * @param array $regionList
     */
    public function setRegionList($regionList)
    {
        $this->regionList = $regionList;
    }

    /**
     * @return array
     */
    public function getBrandsList()
    {
        $brands = $this->getBrandsGateway()->getBrandsList($this->getGeneratorSettingsBrandIds());
        array_unshift($brands, [
            'id' => 0,
            'title' => 'Все'
        ]);
        return ArrayHelper::map($brands, 'id', 'title');
    }

    /**
     * @return BrandsGateway
     */
    protected function getBrandsGateway()
    {
        static $gateway;
        if (is_null($gateway)) {
            $gateway = BrandsGateway::factory($this->shop);
        }

        return $gateway;
    }

    /**
     * @return \int[]
     */
    public function getBrandIds()
    {
        if (is_null($this->brandIds)) {
            $this->brandIds = ArrayHelper::getColumn($this->campaignTemplateBrands, 'brand_id');
        }

        return $this->brandIds;
    }

    /**
     * @param \int[] $brandIds
     */
    public function setBrandIds($brandIds)
    {
        $this->brandIds = $brandIds;
    }

    /**
     * @return array
     */
    public function getMinusRegionList()
    {
        if (is_null($this->minusRegionList)) {
            $this->minusRegionList = array_filter(explode(',', $this->regions), function ($region) {
                return $region < 0;
            });
            $this->minusRegionList = array_map(function ($region) {
                return abs($region);
            }, $this->minusRegionList);
        }
        return $this->minusRegionList;
    }

    /**
     * @param array $minusRegionList
     */
    public function setMinusRegionList($minusRegionList)
    {
        $this->minusRegionList = $minusRegionList;
    }

    /**
     * @inheritDoc
     */
    public function beforeSave($insert)
    {
        $regions = $this->regionList;
        if (!empty($this->minusRegionList)) {
            $regions = array_merge($regions, array_map(
                    function ($region) {
                        return $region * (-1);
                    },
                    $this->minusRegionList)
            );
        }
        $this->regions = implode(',', $regions);

        return parent::beforeSave($insert);
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        $existsBrandIds = ArrayHelper::getColumn($this->campaignTemplateBrands, 'brand_id');
        $newBrandIds = array_diff((array)$this->getBrandIds(), $existsBrandIds);
        $removeBrandIds = array_diff($existsBrandIds, (array)$this->getBrandIds());

        if (!empty($removeBrandIds)) {
            CampaignTemplateBrand::deleteAll([
                'campaign_template_id' => $this->primaryKey,
                'brand_id' => $removeBrandIds
            ]);
        }

        foreach ($newBrandIds as $newBrandId) {
            $campaignTemplateBrand = new CampaignTemplateBrand([
                'campaign_template_id' => $this->primaryKey,
                'brand_id' => $newBrandId
            ]);
            $campaignTemplateBrand->save();
        }
    }
}
