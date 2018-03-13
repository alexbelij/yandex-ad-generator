<?php

use yii\db\Migration;

class m170115_024104_add_status extends Migration
{
    public function up()
    {
        $this->addColumn('ad_yandex_campaign', 'status', $this->string()->comment('Результат операции, выполненной над объявлением'));
        $this->addColumn('ad_yandex_campaign', 'state', $this->string()->comment('Отражает текущее состояние объявления'));
        $this->addColumn('ad_yandex_campaign', 'yandex_group_name', $this->string()->comment('Название группы в директе'));
    }

    public function down()
    {
        $this->dropColumn('ad_yandex_campaign', 'status');
        $this->dropColumn('ad_yandex_campaign', 'state');
        $this->dropColumn('ad_yandex_campaign', 'yandex_group_name');
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
