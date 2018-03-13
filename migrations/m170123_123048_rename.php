<?php

use yii\db\Migration;

class m170123_123048_rename extends Migration
{
    public function up()
    {
        $this->renameColumn('external_product', 'is_available_on_link', 'is_url_available');
    }

    public function down()
    {
        $this->renameColumn('external_product', 'is_url_available', 'is_available_on_link');
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
