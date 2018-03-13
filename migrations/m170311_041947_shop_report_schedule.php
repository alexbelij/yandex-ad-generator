<?php

use yii\db\Migration;

class m170311_041947_shop_report_schedule extends Migration
{
    public function up()
    {
        $this->addColumn('shop', 'is_autoupdate', $this->boolean()->comment('Использовать автообновление'));
        $this->addColumn('shop', 'schedule_autoupdate', $this->string()->comment('Расписание автообновления'));
    }

    public function down()
    {
        $this->dropColumn('shop', 'is_autoupdate');
        $this->dropColumn('shop', 'schedule_autoupdate');
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
