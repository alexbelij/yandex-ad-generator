<?php

use yii\db\Migration;

class m161022_100433_check extends Migration
{
    public function up()
    {
        $this->addColumn('ad', 'is_require_verification', $this->boolean()->defaultValue(false));
    }

    public function down()
    {
        $this->dropColumn('ad', 'is_require_verification');
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
