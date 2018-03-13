<?php

use yii\db\Migration;

class m161022_064900_task_log extends Migration
{
    public function up()
    {
        $this->addColumn('task_queue', 'log_file', $this->string(1024));
    }

    public function down()
    {
        $this->dropColumn('task_queue', 'log_file');
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
