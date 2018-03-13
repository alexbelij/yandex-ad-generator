<?php

use yii\db\Migration;

class m170308_104124_serving_status_to_yandex_group extends Migration
{
    public function up()
    {
        $this->addColumn('ad_yandex_group', 'serving_status', $this->string()->defaultValue('eligible')->comment('Serving status'));
    }

    public function down()
    {
        $this->dropColumn('ad_yandex_group', 'serving_status');
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
