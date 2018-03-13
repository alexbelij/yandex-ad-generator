<?php

use yii\db\Migration;

class m160718_155157_migrate_data extends Migration
{
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->createAndUpdateCampaignTemplate();
        $this->moveAd();

    }

    protected function moveAd()
    {
        /** @var \app\models\Ad[] $ads */
        $ads = \app\models\Ad::find()
            ->with('product')
            ->all();

        foreach ($ads as $ad) {
            $adYandexCampaign = new \app\models\AdYandexCampaign([
                'ad_id' => $ad->primaryKey,
                'yandex_campaign_id' => $ad->product->yandex_campaign_id,
                'template_id' => $ad->template_id,
                'yandex_ad_id' => $ad->yandex_ad_id,
                'yandex_adgroup_id' => $ad->yandex_adgroup_id,
                'uploaded_at' => $ad->uploaded_at
            ]);
            $adYandexCampaign->save();
        }
    }

    protected function createAndUpdateCampaignTemplate()
    {
        /** @var \app\models\Shop[] $shops */
        $shops = \app\models\Shop::find()->all();
        \app\models\CampaignTemplate::deleteAll();
        \app\models\YandexCampaign::updateAll(['campaign_template_id' => null]);
        \app\models\AdTemplate::updateAll(['campaign_template_id' => null]);
        $campaignsByShop = [];
        foreach ($shops as $shop) {
            $campaignTemplate = $this->createCampaignTemplate($shop);

            $campaignsByShop[$shop->primaryKey] = $campaignTemplate;
            \app\models\YandexCampaign::updateAll(
                ['campaign_template_id' => $campaignTemplate->primaryKey],
                ['shop_id' => $shop->primaryKey]
            );
            \app\models\AdTemplate::updateAll(
                ['campaign_template_id' => $campaignTemplate->primaryKey],
                ['shop_id' => $shop->primaryKey]
            );
        }
    }

    /**
     * @param \app\models\Shop $shop
     * @return \app\models\CampaignTemplate
     */
    protected function createCampaignTemplate(\app\models\Shop $shop)
    {
        $campaignTemplate = new \app\models\CampaignTemplate([
            'regions' => '1',
            'title' => 'Создана автоматически для ' . $shop->name,
            'shop_id' => $shop->primaryKey,
            'negative_keywords' => '',
            'text_campaign' => '{"biddingStrategySearchType":"LOWEST_COST","biddingStrategyNetworkType":"SERVING_OFF","settings":{"ADD_METRICA_TAG":"YES","ENABLE_AREA_OF_INTEREST_TARGETING":"NO","ENABLE_SITE_MONITORING":"YES","ENABLE_RELATED_KEYWORDS":"NO","EXCLUDE_PAUSED_COMPETING_ADS":"YES"}}'
        ]);
        $campaignTemplate->save();

        return $campaignTemplate;
    }

    public function safeDown()
    {

    }
}
