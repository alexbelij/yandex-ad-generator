<?php

use yii\db\Migration;

class m160903_005731_alter_shop extends Migration
{
    public function up()
    {
        $this->addColumn('shop', 'schedule', $this->text());
        $this->addColumn('shop', 'remote_file_url', $this->string(1024));
    }

    public function down()
    {
        $this->dropColumn('shop', 'schedule');
        $this->dropColumn('shop', 'remote_file_url');
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
