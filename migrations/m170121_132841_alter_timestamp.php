<?php

use yii\db\Migration;

class m170121_132841_alter_timestamp extends Migration
{
    public function up()
    {
        Yii::$app->db->createCommand('ALTER TABLE `ad`
	      CHANGE COLUMN `updated_at` `updated_at` TIMESTAMP NULL DEFAULT NULL COMMENT \'Дата обновления объявления\' AFTER `created_at`;')->execute();
        Yii::$app->db->createCommand('ALTER TABLE `ad`
	      CHANGE COLUMN `created_at` `created_at` TIMESTAMP NULL DEFAULT NULL COMMENT \'Дата создания объявления\' AFTER `keywords`;')->execute();
        Yii::$app->db->createCommand('ALTER TABLE `ad`
	      CHANGE COLUMN `generated_at` `generated_at` TIMESTAMP NULL DEFAULT NULL COMMENT \'Дата генерации объявления\' AFTER `updated_at`;')->execute();
        Yii::$app->db->createCommand('ALTER TABLE `ad`
	CHANGE COLUMN `created_at` `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP COMMENT \'Дата создания объявления\' AFTER `keywords`;')->execute();

        Yii::$app->db->createCommand('ALTER TABLE `product`
	CHANGE COLUMN `updated_at` `updated_at` TIMESTAMP NULL DEFAULT NULL AFTER `created_at`;')->execute();

    }

    public function down()
    {
        return true;
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
