<?php

use yii\db\Migration;

class m170408_073247_feed extends Migration
{
    public function up()
    {
        $this->createTable('feed', [
            'id' => $this->primaryKey(),
            'title' => $this->string()->notNull(),
            'domain' => $this->string()->notNull()
        ]);

        $this->createTable('feed_redirect', [
            'id' => $this->primaryKey(),
            'feed_id' => $this->integer()->comment('Фид'),
            'hash_url' => $this->string()->comment('Хэш урла'),
            'target_url' => $this->string(2048)->comment('Урл, на который редиректить')
        ]);

        $this->addForeignKey(
            'fk_feed_redirect_feed_id',
            'feed_redirect',
            'feed_id',
            'feed',
            'id',
            'cascade',
            'cascade'
        );

        $this->createIndex(
            'idx_feed_redirect_hash_url', 'feed_redirect', 'hash_url', true
        );
    }

    public function down()
    {
        $this->dropForeignKey('fk_feed_redirect_feed_id', 'feed_redirect');
        $this->dropTable('feed_redirect');
        $this->dropTable('feed');
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
