<?php

use yii\db\Migration;

class m170806_011542_feed extends Migration
{
    public function up()
    {
        $this->createTable('feed_category', [
            'id' => $this->integer()->comment('Ид категории'),
            'feed_queue_id' => $this->integer()->comment('Загруженный фид')->unsigned(),
            'title' => $this->string()->comment('Название категории'),
            'parent_id' => $this->integer()->comment('Родительская категория')
        ]);

        $this->createTable('feed_brand', [
            'id' => $this->primaryKey()->unsigned(),
            'feed_queue_id' => $this->integer()->comment('Загруженный фид')->unsigned(),
            'title' => $this->string()->comment('Название бренда')
        ]);

        $this->addPrimaryKey('pk_feed_category', 'feed_category', ['id', 'feed_queue_id']);

        $this->createTable('feed_item', [
            'id' => $this->primaryKey()->unsigned(),
            'feed_queue_id' => $this->integer()->comment('Загруженный фид')->unsigned(),
            'brand_id' => $this->integer()->unsigned()->comment('Бренд'),
            'category_id' => $this->integer()->comment('Категория'),
            'price' => $this->bigInteger()->comment('Цена'),
            'is_active' => $this->boolean()->comment('Активность')->defaultValue(true),
            'item_text' => $this->text()->comment('Содержимое элемента')
        ]);

        $this->addColumn('feed_queue', 'template', $this->text()->comment('Шаблон фида'));

        $this->addForeignKey(
            'fk_feed_category_feed',
            'feed_category',
            'feed_queue_id',
            'feed_queue',
            'id',
            'cascade',
            'cascade'
        );

        $this->addForeignKey(
            'fk_feed_brand_feed',
            'feed_brand',
            'feed_queue_id',
            'feed_queue',
            'id',
            'cascade',
            'cascade'
        );

        $this->addForeignKey(
            'fk_feed_item_feed',
            'feed_item',
            'feed_queue_id',
            'feed_queue',
            'id',
            'cascade',
            'cascade'
        );

        $this->addForeignKey(
            'fk_feed_item_brand',
            'feed_item',
            'brand_id',
            'feed_brand',
            'id',
            'cascade',
            'cascade'
        );

        $this->addForeignKey(
            'fk_feed_item_category',
            'feed_item',
            ['feed_queue_id', 'category_id'],
            'feed_category',
            ['feed_queue_id', 'id'],
            'cascade',
            'cascade'
        );
    }

    public function down()
    {
        $this->dropForeignKey('fk_feed_category_feed', 'feed_category');
        $this->dropForeignKey('fk_feed_brand_feed', 'feed_brand');
        $this->dropForeignKey('fk_feed_item_feed', 'feed_item');
        $this->dropForeignKey('fk_feed_item_brand', 'feed_item');
        $this->dropForeignKey('fk_feed_item_category', 'feed_item');

        $this->dropTable('feed_category');
        $this->dropTable('feed_brand');
        $this->dropTable('feed_item');

        $this->dropColumn('feed_queue', 'template');
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
