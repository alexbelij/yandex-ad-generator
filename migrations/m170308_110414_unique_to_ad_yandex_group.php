<?php

use yii\db\Migration;

class m170308_110414_unique_to_ad_yandex_group extends Migration
{
    public function up()
    {
        $this->createIndex('idx-ad_yandex_group-yandex_adgroup_id', 'ad_yandex_group', 'yandex_adgroup_id', true);
    }

    public function down()
    {
        $this->dropIndex('idx-ad_yandex_group-yandex_adgroup_id', 'ad_yandex_group');
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
