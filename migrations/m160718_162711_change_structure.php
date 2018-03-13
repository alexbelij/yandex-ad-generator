<?php

use yii\db\Migration;

class m160718_162711_change_structure extends Migration
{
    public function up()
    {
        $this->dropForeignKey('products_yacampaign_fk', '{{%product}}');
        //$this->dropForeignKey('ads_template_fk', '{{%ad}}');

        $this->dropColumn('{{%ad}}', 'template_id');
        $this->dropColumn('{{%ad}}', 'yandex_ad_id');
        $this->dropColumn('{{%ad}}', 'yandex_adgroup_id');
        $this->dropColumn('{{%ad}}', 'uploaded_at');

        $this->dropColumn('{{%product}}', 'yandex_campaign_id');

        $this->db->createCommand('ALTER TABLE yandex_update_log MODIFY COLUMN entity_id int NULL ')->execute();
    }

    public function down()
    {
        echo "m160718_162711_change_structure cannot be reverted.\n";

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
