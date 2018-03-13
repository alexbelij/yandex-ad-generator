<?php

use yii\db\Migration;

class m170826_013147_alter_update_log extends Migration
{
    public function up()
    {
        $this->alterColumn('yandex_update_log', 'entity_id', $this->bigInteger());
    }

    public function down()
    {
        echo "m170826_013147_alter_update_log cannot be reverted.\n";
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
