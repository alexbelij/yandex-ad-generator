<?php

use yii\db\Migration;

class m160904_014157_use_schedule extends Migration
{
    public function up()
    {
        $this->addColumn('shop', 'is_use_schedule', $this->boolean()->defaultValue(false));
    }

    public function down()
    {
        $this->dropColumn('shop', 'is_use_schedule');
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
