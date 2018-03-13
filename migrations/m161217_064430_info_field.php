<?php

use yii\db\Migration;

class m161217_064430_info_field extends Migration
{
    public function up()
    {
        $this->addColumn('task_queue', 'info', $this->string(1024));
    }

    public function down()
    {
        $this->dropColumn('task_queue', 'info');
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
