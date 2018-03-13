<?php

use yii\db\Migration;

class m160816_160852_ad_product_fk extends Migration
{
    public function up()
    {
        $this->addForeignKey('fk_ad_product', 'ad', 'product_id', 'product', 'id', 'cascade', 'cascade');
    }

    public function down()
    {
        echo "m160816_160852_ad_product_fk cannot be reverted.\n";

        return false;
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
