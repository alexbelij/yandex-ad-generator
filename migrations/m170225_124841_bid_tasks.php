<?php

use yii\db\Migration;

class m170225_124841_bid_tasks extends Migration
{
    public function up()
    {
        $this->createTable('bid_task', [
            'id' => $this->primaryKey()->unsigned(),
            'created_at' => $this->timestamp()->defaultValue(null)->comment('Время создания'),
            'started_at' => $this->timestamp()->defaultValue(null)->comment('Время запуска'),
            'finished_at' => $this->timestamp()->defaultValue(null)->comment('Время завершения'),
            'account_id' => $this->integer()->comment('Аккаунт'),
            'status' => $this->string()->comment('Статус'),
            'task' => $this->string()->comment('Задача'),
            'context' => $this->text()->comment('Контекст'),
            'message' => $this->text()->comment('Сообщение')
        ]);

        $this->createTable('bid_task_log', [
            'id' => $this->primaryKey()->unsigned(),
            'task_id' => $this->integer()->unsigned()->comment('Задача'),
            'created_at' => $this->timestamp()->defaultValue(null)->comment('Время создания'),
            'level' => $this->string()->comment('Тип сообщения'),
            'message' => $this->text()->comment('Сообщение'),
            'context' => $this->text()->comment('Контекст')
        ]);

        $this->addForeignKey(
            'fk_bid_task_log_task_id',
            'bid_task_log',
            'task_id',
            'bid_task',
            'id',
            'cascade'
        );

        $this->addForeignKey(
            'fk_bid_task_account_id',
            'bid_task',
            'account_id',
            'bid_account',
            'id',
            'cascade'
        );

        $this->createIndex(
            'idx_account_id_status_task',
            'bid_task',
            [
                'task', 'status'
            ]
        );
    }

    public function down()
    {
        $this->dropTable('bid_task_log');
        $this->dropTable('bid_task');
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
