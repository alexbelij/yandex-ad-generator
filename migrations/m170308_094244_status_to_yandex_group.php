<?php

use yii\db\Migration;

class m170308_094244_status_to_yandex_group extends Migration
{
    public function up()
    {
        $this->addColumn('ad_yandex_group', 'status', $this->string()->defaultValue('draft')->comment('Status'));
    }

    public function down()
    {
        $this->dropColumn('ad_yandex_group', 'status');
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
