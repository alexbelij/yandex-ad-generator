<?php

use yii\db\Migration;

class m170820_012330_keyword_yandex_ad extends Migration
{
    public function up()
    {
        $this->addColumn('ad_keyword', 'yandex_id', $this->bigInteger()->comment('Yandex id'));
        $this->createIndex('idx_yandex_id', 'ad_keyword', 'yandex_id');
    }

    public function down()
    {
        $this->dropColumn('ad_keyword', 'yandex_id');
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
