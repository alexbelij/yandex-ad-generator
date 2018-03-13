<?php

use yii\db\Migration;

class m160823_150219_file_upload extends Migration
{
    public function up()
    {
        $this->execute('CREATE TABLE IF NOT EXISTS `file_import` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `original_filename` varchar(255) DEFAULT NULL,
  `filename` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `size` int(11) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `error_msg` varchar(255) DEFAULT NULL,
  `shop_id` INT(11) DEFAULT null,
  `is_loaded` tinyint(1) DEFAULT 0,
  `company_name` VARCHAR(255) DEFAULT NULL,
  `catalog_date` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8');
    }

    public function down()
    {
        $this->dropTable('file_import');
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
