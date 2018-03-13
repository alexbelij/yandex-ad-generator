<?php

use yii\db\Migration;

class m160809_135710_sort_template extends Migration
{
    public function up()
    {
        $this->addColumn('ad_template', 'sort', $this->integer());
    }

    public function down()
    {
        $this->dropColumn('ad_template', 'sort');
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
