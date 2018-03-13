<?php

use yii\db\Migration;

class m160723_053337_fk_change extends Migration
{
    public function up()
    {
        $this->dropForeignKey('fk_ad_yandex_campaign_template', 'ad_yandex_campaign');
        $this->addForeignKey(
            'fk_ad_yandex_campaign_template',
            'ad_yandex_campaign',
            'template_id',
            'ad_template',
            'id',
            'set null'
        );
    }

    public function down()
    {
        echo "m160723_053337_fk_change cannot be reverted.\n";

        return false;
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
