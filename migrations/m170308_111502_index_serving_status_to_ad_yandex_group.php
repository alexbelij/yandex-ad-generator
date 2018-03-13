<?php

use yii\db\Migration;

class m170308_111502_index_serving_status_to_ad_yandex_group extends Migration
{
    public function up()
    {
        $this->createIndex('idx-ad_yandex_group-serving_status', 'ad_yandex_group', ['status', 'serving_status']);
    }

    public function down()
    {
        $this->dropIndex('idx-ad_yandex_group-serving_status', 'ad_yandex_group');
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
