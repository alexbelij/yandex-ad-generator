<?php

use yii\db\Migration;

class m170304_120411_is_manual_brand extends Migration
{
    public function up()
    {
        $this->addColumn('external_brand', 'is_manual', $this->boolean()->defaultValue(false));
        $this->addColumn('external_category', 'is_manual', $this->boolean()->defaultValue(false));
    }

    public function down()
    {
        $this->dropColumn('external_brand', 'is_manual');
        $this->dropColumn('external_category', 'is_manual');
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
