<?php

use yii\db\Migration;

class m160826_040154_indexes extends Migration
{
    public function up()
    {
        $this->createIndex('external_product_shop_id_idx', 'external_product', 'shop_id');
        $this->createIndex('external_product_title_idx', 'external_product', 'title');
        $this->createIndex('external_product_price_idx', 'external_product', 'price');
        $this->createIndex('external_product_brand_idx', 'external_product', 'brand_id');
        $this->createIndex('external_product_updated_idx', 'external_product', 'updated_at');
    }

    public function down()
    {
        $this->dropIndex('external_product_shop_id_idx', 'external_product');
        $this->dropIndex('external_product_title_idx', 'external_product');
        $this->dropIndex('external_product_price_idx', 'external_product');
        $this->dropIndex('external_product_brand_idx', 'external_product');
        $this->dropIndex('external_product_updated_idx', 'external_product');
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
