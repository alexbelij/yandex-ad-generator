<?php

use yii\db\Migration;

class m160723_130401_points extends Migration
{
    public function up()
    {
        $this->addColumn('yandex_update_log', 'points', $this->integer());

        $this->createTable('campaign_template_brand', [
            'campaign_template_id' => $this->integer(),
            'brand_id' => $this->integer()
        ], 'engine=innodb');

        $this->addPrimaryKey('pk_campaign_template_brand', 'campaign_template_brand', ['campaign_template_id', 'brand_id']);
        $this->addForeignKey(
            'fk_campaign_template_brand_campaign_template',
            'campaign_template_brand',
            'campaign_template_id',
            'campaign_template',
            'id',
            'cascade'
        );
    }

    public function down()
    {
        $this->dropColumn('yandex_update_log', 'points');
        $this->dropTable('campaign_template_brand');
    }

    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}
