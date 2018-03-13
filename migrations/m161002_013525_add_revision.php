<?php

use yii\db\Migration;

class m161002_013525_add_revision extends Migration
{
    public function up()
    {
        $this->addColumn('ad', 'revision', $this->integer()->defaultValue(0));
    }

    public function down()
    {
        $this->dropColumn('ad', 'revision');
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
