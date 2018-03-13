<?php

use yii\db\Migration;

class m160720_142822_add_template_campaign_template extends Migration
{

    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->renameTable('template', 'ad_template');

        $this->createTable('ad_template_campaign_template', [
            'ad_template_id' => $this->integer(),
            'campaign_template_id' => $this->integer()
        ], 'engine=INNODB');

        $this->createIndex(
            'idx_ad_template_campaign_template',
            'ad_template_campaign_template',
            ['ad_template_id', 'campaign_template_id'],
            true
        );

        $this->addForeignKey(
            'fk_ad_template_campaign_template_ad_template_id',
            'ad_template_campaign_template',
            'ad_template_id',
            'ad_template',
            'id',
            'cascade'
        );

        $this->addForeignKey(
            'fk_ad_template_campaign_template_ad_campaign_template_id',
            'ad_template_campaign_template',
            'campaign_template_id',
            'campaign_template',
            'id',
            'cascade'
        );

        $this->dropForeignKey('fk_template_campaign_template', 'ad_template');
        $this->dropColumn('ad_template', 'campaign_template_id');
    }

    public function safeDown()
    {

    }

}
