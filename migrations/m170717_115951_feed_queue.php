<?php

use yii\db\Migration;

class m170717_115951_feed_queue extends Migration
{
    public function up()
    {
        $this->createTable('feed_queue', [
            'id' => $this->primaryKey()->unsigned(),
            'feed_id' => $this->integer()->comment('Фид'),
            'created_at' => $this->timestamp()->defaultValue(null)->comment('Дата создания'),
            'source_file' => $this->string(1024)->comment('Файл донор'),
            'result_file' => $this->string(1024)->comment('Обработанный файл'),
            'finished_at' => $this->timestamp()->defaultValue(null),
            'status' => $this->string(),
            'error_message' => $this->string(2048),
            'original_filename' => $this->string()->comment('Название загружаемого файла'),
            'size' => $this->bigInteger()->comment('Размер файла в байтах')
        ]);

        $this->addForeignKey(
            'fk_feed_queue_feed_id',
            'feed_queue',
            'feed_id',
            'feed',
            'id',
            'cascade'
        );
    }

    public function down()
    {
        $this->dropForeignKey('fk_feed_queue_feed_id', 'feed_queue');
        $this->dropTable('feed_queue');
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
