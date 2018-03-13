<?php

use yii\db\Migration;

class m170122_072733_external_product_available extends Migration
{
    public function up()
    {
        $this->addColumn('external_product', 'is_available_on_link', $this->boolean()->defaultValue(null));
        $this->addColumn('external_product', 'available_check_at', $this->timestamp()->defaultValue(null));
    }

    public function down()
    {
        $this->dropColumn('external_product', 'is_available_on_link');
        $this->dropColumn('external_product', 'available_check_time');
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
