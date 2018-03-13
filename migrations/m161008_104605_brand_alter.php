<?php

use yii\db\Migration;

class m161008_104605_brand_alter extends Migration
{
    public function up()
    {
        $this->addColumn('external_brand', 'outer_id', $this->integer());
    }

    public function down()
    {
        $this->dropColumn('external_brand', 'outer_id');
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
