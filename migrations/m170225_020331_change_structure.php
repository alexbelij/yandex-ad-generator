<?php

use yii\db\Migration;

class m170225_020331_change_structure extends Migration
{
    public function up()
    {
        $this->dropForeignKey('fk_bid_yandex_ad_group_strategy_1', 'bid_yandex_ad_group');
        $this->dropForeignKey('fk_bid_yandex_ad_group_strategy_2', 'bid_yandex_ad_group');

        $this->dropColumn('bid_yandex_ad_group', 'strategy_1');
        $this->dropColumn('bid_yandex_ad_group', 'strategy_2');

        $this->addColumn('bid_yandex_keyword', 'strategy_1', $this->integer()->comment('Основная стратегия'));
        $this->addColumn('bid_yandex_keyword', 'strategy_2', $this->integer()->comment('Доп стратегия'));

        $this->addForeignKey(
            'fk_bid_yandex_keyword_strategy_1',
            'bid_yandex_keyword',
            'strategy_1',
            'bid_strategy',
            'id',
            'set null'
        );

        $this->addForeignKey(
            'fk_bid_yandex_keyword_strategy_2',
            'bid_yandex_keyword',
            'strategy_2',
            'bid_strategy',
            'id',
            'set null'
        );
    }

    public function down()
    {
        $this->dropForeignKey('fk_bid_yandex_keyword_strategy_1', 'bid_yandex_keyword');
        $this->dropForeignKey('fk_bid_yandex_keyword_strategy_2', 'bid_yandex_keyword');

        $this->dropColumn('bid_yandex_keyword', 'strategy_1');
        $this->dropColumn('bid_yandex_keyword', 'strategy_2');

        $this->addColumn('bid_yandex_ad_group', 'strategy_1', $this->integer()->comment('Основная стратегия'));
        $this->addColumn('bid_yandex_ad_group', 'strategy_2', $this->integer()->comment('Доп стратегия'));

        $this->addForeignKey(
            'fk_bid_yandex_ad_group_strategy_1',
            'bid_yandex_ad_group',
            'strategy_1',
            'bid_strategy',
            'id',
            'set null'
        );

        $this->addForeignKey(
            'fk_bid_yandex_ad_group_strategy_2',
            'bid_yandex_ad_group',
            'strategy_2',
            'bid_strategy',
            'id',
            'set null'
        );
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
