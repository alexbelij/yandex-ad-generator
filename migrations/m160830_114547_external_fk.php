<?php

use yii\db\Migration;

class m160830_114547_external_fk extends Migration
{
    public function up()
    {
        $this->addForeignKey('external_product_shop_id_fk', 'external_product', 'shop_id', 'shop', 'id', 'cascade', 'cascade');
        $this->addForeignKey('external_category_shop_id_fk', 'external_category', 'shop_id', 'shop', 'id', 'cascade', 'cascade');
        $this->addForeignKey('external_brand_shop_id_fk', 'external_brand', 'shop_id', 'shop', 'id', 'cascade', 'cascade');
    }

    public function down()
    {
        $this->dropForeignKey('external_product_shop_id_fk', 'external_product');
        $this->dropForeignKey('external_category_shop_id_fk', 'external_category');
        $this->dropForeignKey('external_brand_shop_id_fk', 'external_brand');
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
