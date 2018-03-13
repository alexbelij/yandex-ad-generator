<?php

use yii\db\Migration;

class m170311_063406_yandex_sitelinks extends Migration
{
    public function up()
    {
        $this->createTable('yandex_sitelink', [
            'id' => $this->primaryKey()->unsigned(),
            'yandex_id' => $this->bigInteger()->comment('Уникальный идентификатор в директе'),
            'shop_id' => $this->integer()->comment('Магазин'),
            'account_id' => $this->integer()->comment('Аккаунт')
        ]);
    }

    public function down()
    {
        $this->dropTable('yandex_sitelink');
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
