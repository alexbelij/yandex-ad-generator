<?php

use yii\db\Migration;

class m170227_114238_change_max_click_type extends Migration
{
    public function up()
    {
        $this->alterColumn('bid_account', 'max_click_price', $this->integer()->comment('Максимальная цена клика'));
        $this->alterColumn('bid_yandex_keyword', 'max_click_price', $this->integer()->comment('Максимальная цена клика'));
        $this->alterColumn('bid_yandex_campaign', 'max_click_price', $this->integer()->comment('Максимальная цена клика'));

        $this->addColumn('bid_account', 'strategy_1', $this->integer()->comment('Основная стратегия'));
        $this->addColumn('bid_account', 'strategy_2', $this->integer()->comment('Дополнительная стратегия'));
        $this->addColumn('bid_account', 'units', $this->string()->comment('Баллы'));

        $this->addColumn('bid_task', 'total_points', $this->integer()->unsigned()->comment('Потраченные баллы'));

        $this->addForeignKey(
            'fk_bid_account_strategy_1',
            'bid_account',
            'strategy_1',
            'bid_strategy',
            'id',
            'set null'
        );

        $this->addForeignKey(
            'fk_bid_account_strategy_2',
            'bid_account',
            'strategy_2',
            'bid_strategy',
            'id',
            'set null'
        );
    }

    public function down()
    {
        $this->dropForeignKey('fk_bid_account_strategy_1', 'bid_account');
        $this->dropForeignKey('fk_bid_account_strategy_2', 'bid_account');

        $this->dropColumn('bid_account', 'strategy_1');
        $this->dropColumn('bid_account', 'strategy_2');
        $this->dropColumn('bid_account', 'units');
        $this->dropColumn('bid_task', 'total_points');
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
