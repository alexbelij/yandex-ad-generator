<?php

use yii\db\Migration;

class m160724_055323_total_points extends Migration
{
    public function up()
    {
        $this->addColumn('task_queue', 'total_points', $this->integer());
    }

    public function down()
    {
        echo "m160724_055323_total_points cannot be reverted.\n";

        return false;
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
