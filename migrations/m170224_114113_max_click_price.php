<?php

use yii\db\Migration;

class m170224_114113_max_click_price extends Migration
{
    public function up()
    {
        $this->addColumn('bid_account', 'max_click_price', $this->decimal(10,2)->comment('Максимальная цена клика'));
        $this->addColumn('bid_yandex_keyword', 'max_click_price', $this->decimal(10,2)->comment('Максимальная цена клика'));
        $this->addColumn('bid_yandex_campaign', 'max_click_price', $this->decimal(10,2)->comment('Максимальная цена клика'));

        $this->addColumn('bid_strategy', 'strategy', $this->string());
    }

    public function down()
    {
        $this->dropColumn('bid_account', 'max_click_price');
        $this->dropColumn('bid_yandex_keyword', 'max_click_price');
        $this->dropColumn('bid_yandex_campaign', 'max_click_price');
        $this->dropColumn('bid_strategy', 'strategy');
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
