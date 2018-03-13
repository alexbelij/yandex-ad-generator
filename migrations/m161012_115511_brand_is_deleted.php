<?php

use yii\db\Migration;

class m161012_115511_brand_is_deleted extends Migration
{
    public function up()
    {
        $this->addColumn('external_brand', 'is_deleted', $this->boolean()->defaultValue(false));
        $this->dropColumn('external_product', 'description');
    }

    public function down()
    {
        $this->dropColumn('external_brand', 'is_deleted');
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
