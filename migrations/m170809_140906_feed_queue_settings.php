<?php

use yii\db\Migration;

class m170809_140906_feed_queue_settings extends Migration
{
    public function up()
    {
        $this->createTable('feed_settings', [
            'id' => $this->primaryKey(),
            'feed_queue_id' => $this->integer()->comment('Фид')->unsigned(),
            'title' => $this->string()->comment('Заголовок'),
            'settings' => $this->text()->comment('Настройки')
        ]);

        $this->addForeignKey(
            'fk_feed_search_feed_queue',
            'feed_settings',
            'feed_queue_id',
            'feed_queue',
            'id',
            'cascade'
        );
    }

    public function down()
    {
        $this->dropForeignKey('fk_feed_search_feed_queue', 'feed_settings');
        $this->dropTable('feed_settings');
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
