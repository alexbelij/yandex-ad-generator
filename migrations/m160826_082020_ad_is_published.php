<?php

use yii\db\Migration;

class m160826_082020_ad_is_published extends Migration
{
    public function up()
    {
        $this->addColumn('ad_yandex_campaign', 'is_published', $this->boolean()->defaultValue(false));
    }

    public function down()
    {
        $this->dropColumn('ad_yandex_campaign', 'is_published');
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
