<?php

use yii\db\Migration;

class m161106_090337_alter_update_log extends Migration
{
    public function up()
    {
        $this->alterColumn('yandex_update_log', 'message', $this->string(2048));
    }

    public function down()
    {
        $this->alterColumn('yandex_update_log', 'message', $this->string(300));
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
