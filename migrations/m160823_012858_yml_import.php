<?php

use yii\db\Migration;

class m160823_012858_yml_import extends Migration
{
    public function up()
    {
        $this->execute('CREATE TABLE IF NOT EXISTS `external_product` (
  `id` int(11) PRIMARY KEY AUTO_INCREMENT,
  `outer_id` int(11) DEFAULT NULL,
  `shop_id` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `model` VARCHAR(255) DEFAULT NULL,
  `description` text,
  `brand_id` int(11) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `is_available` tinyint(4) DEFAULT NULL,
  `picture` varchar(1024) DEFAULT NULL,
  `url` varchar(1024) DEFAULT NULL,
  `currency_id` varchar(11) DEFAULT NULL,
  `old_price` float DEFAULT NULL,
  `price` float DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `file_import_id` INT(11)
) ENGINE=InnoDB DEFAULT CHARSET=utf8');

        $this->execute('CREATE TABLE IF NOT EXISTS `external_category` (
  `id` int(11) PRIMARY KEY AUTO_INCREMENT,
  `outer_id` int(11) DEFAULT NULL,
  `shop_id` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8');

        $this->execute('CREATE TABLE IF NOT EXISTS `external_brand` (
  `id` int(11) PRIMARY KEY AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `shop_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8');

        $this->addForeignKey(
            'fk_external_product_category',
            'external_product',
            'category_id',
            'external_category',
            'id',
            'cascade',
            'cascade'
        );
        $this->createIndex('external_product_outer_id_shop_id_idx', 'external_product', ['outer_id', 'shop_id'], true);
        $this->createIndex('external_category_outer_id_shop_id_idx', 'external_category', ['outer_id', 'shop_id'], true);

        $this->addColumn('shop', 'external_strategy', $this->string());
    }

    public function down()
    {
        $this->dropForeignKey('fk_external_product_category', 'external_product');
        $this->dropIndex('external_product_outer_id_shop_id_idx', 'external_product');
        $this->dropIndex('external_category_outer_id_shop_id_idx', 'external_category');

        $this->dropTable('external_product');
        $this->dropTable('external_category');
        $this->dropTable('external_brand');

        $this->dropColumn('shop', 'external_strategy');
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
