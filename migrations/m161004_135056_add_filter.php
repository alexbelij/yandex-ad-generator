<?php

use yii\db\Migration;

class m161004_135056_add_filter extends Migration
{
    public function up()
    {
        $this->addColumn('{{%generator_settings}}', 'filter', $this->text());
    }

    public function down()
    {
        $this->dropColumn('{{%generator_settings}}', 'filter');
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
