<?php

use yii\db\Migration;

class m161210_022629_alter_ext_products extends Migration
{
    public function up()
    {
        $this->addColumn('external_product', 'is_manual', $this->boolean()->defaultValue(false));
    }

    public function down()
    {
        $this->dropColumn('external_product', 'is_manual');
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
