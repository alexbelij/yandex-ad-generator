<?php

use yii\db\Migration;

class m160825_151813_file_import_log extends Migration
{
    public function up()
    {
        $this->execute('CREATE TABLE `file_import_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `file_import_id` int(11) DEFAULT NULL,
  `title` varchar(250) DEFAULT NULL,
  `operation` varchar(50) DEFAULT NULL,
  `old_value` text,
  `new_value` text,
  `entity_type` varchar(20) DEFAULT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8');

        $this->addForeignKey(
            'file_import_log_file_import_fk',
            'file_import_log',
            'file_import_id',
            'file_import',
            'id',
            'cascade',
            'cascade'
        );

        $this->createIndex('file_import_lod_file_import_id_idx', 'file_import_log', 'file_import_id');
        $this->createIndex('file_import_log_entity_idx', 'file_import_log', ['entity_id', 'entity_type']);
    }

    public function down()
    {
        $this->dropForeignKey('file_import_log_file_import_fk', 'file_import_log');
        $this->dropTable('file_import_log');
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
