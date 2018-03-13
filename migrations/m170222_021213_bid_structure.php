<?php

use yii\db\Migration;

class m170222_021213_bid_structure extends Migration
{
    public function up()
    {
        $this->createTable('bid_account', [
            'id' => $this->primaryKey(),
            'title' => $this->string(),
            'token' => $this->string(),
            'settings' => $this->text(),
            'type' => $this->string(),
            'last_updated_at' => $this->timestamp()->defaultValue(null),
        ]);

        $this->createTable('bid_strategy', [
            'id' => $this->primaryKey(),
            'title' => $this->string()->comment('Заголовок стратегии'),
            'delegee' => $this->string()->comment('Класс стратегии')
        ]);

        $this->createTable('bid_yandex_campaign', [
            'id' => $this->bigInteger()->unsigned()->comment('Id кампании'),
            'account_id' => $this->integer()->comment('Аккаунт'),
            'created_at' => $this->timestamp()->defaultValue(null),
            'updated_at' => $this->timestamp()->defaultValue(null),
            'title' => $this->string()->comment('Название кампании'),
            'start_date' => $this->date()->defaultValue(null)->comment('Дата начала показа объявлений'),
            'end_date' => $this->date()->defaultValue(null)->comment('Дата окончания показов объявлений'),
            'status' => $this->string()->comment('Статус кампании'),
            'state' => $this->string()->comment('Состояние кампании'),
            'status_payment' => $this->string()->comment('Статус оплаты кампании'),
            'status_clarification' => $this->string()->comment('Текстовое пояснение к статусу'),
            'stat_clicks' => $this->bigInteger()->comment('Количество кликов за время существования кампании'),
            'stat_impressions' => $this->bigInteger()->comment('Количество показов за время существования кампании'),
            'currency' => $this->string()->comment('Валюта'),
            'funds_mode' => $this->string()->comment('Тип финансовых показателей кампании'),
            'funds_sum' => $this->decimal(10, 2)->comment('Сумма средств, зачисленных на баланс кампании за время ее существования, в валюте рекламодателя, без НДС'),
            'funds_balance' => $this->decimal(10, 2)->comment('Текущий баланс кампании в валюте рекламодателя, без НДС'),
            'funds_shared_refund' => $this->decimal(10, 2)->comment('Сумма возврата средств за клики, признанные системой недобросовестными или ошибочными, без НДС'),
            'funds_shared_spend' => $this->decimal(10, 2)->comment('Сумма средств, израсходованных по данной кампании за все время ее существования, без НДС'),
            'client_info' => $this->string()->comment('Название клиента'),
            'daily_budget_amount' => $this->decimal(10, 2)->comment('Дневной бюджет кампании в валюте рекламодателя'),
            'daily_budget_mode' => $this->string()->comment('Тип дневного бюджета'),

            'strategy_1' => $this->integer()->comment('Стратегия 1'),
            'strategy_2' => $this->integer()->comment('Стратегия 2'),
        ]);

        $this->createTable('bid_yandex_ad_group', [
            'id' => $this->bigInteger()->unsigned()->comment('Id группы'),
            'campaign_id' => $this->bigInteger()->comment('Кампания'),
            'account_id' => $this->integer()->comment('Аккаунт'),
            'created_at' => $this->timestamp()->defaultValue(null),
            'updated_at' => $this->timestamp()->defaultValue(null),
            'name' => $this->string()->comment('Название группы'),
            'status' => $this->string()->comment('Статус группы'),
            'type' => $this->string()->comment('Тип группы объявлений'),

            'strategy_1' => $this->integer()->comment('Стратегия 1'),
            'strategy_2' => $this->integer()->comment('Стратегия 2'),
        ]);

        $this->createTable('bid_yandex_keyword', [
            'id' => $this->bigInteger()->unsigned()->comment('Id'),
            'keyword' => $this->string()->comment('Ключевое слово'),
            'group_id' => $this->bigInteger()->comment('Группа объявления'),
            'campaign_id' => $this->bigInteger()->comment('Кампания'),
            'account_id' => $this->integer()->comment('Аккаунт'),
            'created_at' => $this->timestamp()->defaultValue(null),
            'updated_at' => $this->timestamp()->defaultValue(null),
            'bid' => $this->decimal(10, 2)->comment('Ставка на поиске'),
            'context_bid' => $this->decimal(10, 2)->comment('Ставка в сетях'),
            'state' => $this->string()->comment('Состояние ключевой фразы'),
            'status' => $this->string()->comment('Статус ключевой фразы'),
            'stat_search_clicks' => $this->bigInteger()->comment('Количество кликов по всем объявлениям группы, показанным по данной фразе'),
            'stat_search_impressions' => $this->bigInteger()->comment('Количество показов всех объявлений группы по данной фразе'),
            'stat_network_clicks' => $this->bigInteger()->comment('Количество кликов по всем объявлениям группы, показанным по данной фразе'),
            'stat_network_impressions' => $this->bigInteger()->comment('Количество показов всех объявлений группы по данной фразе'),
        ]);

        $this->createTable('bid_yandex_bid', [
            'id' => $this->bigInteger()->unsigned(),
            'campaign_id' => $this->bigInteger()->comment('Кампания'),
            'group_id' => $this->bigInteger()->comment('Группа объявления'),
            'keyword_id' => $this->bigInteger()->comment('Ключевое слово'),
            'created_at' => $this->timestamp()->defaultValue(null),
            'updated_at' => $this->timestamp()->defaultValue(null),

            'bid_serving_status' => $this->string()->comment('Статус возможности показов группы объявлений'),
            'bid' => $this->decimal(10, 2)->comment('Ставка на поиске'),
            'context_bid' => $this->decimal(10, 2)->comment('Ставка в сетях'),
            'bid_min_search_price' => $this->decimal(10, 2)->comment('Минимальная ставка, установленная для рекламодателя, при которой возможен показ на поиске'),
            'bid_current_search_price' => $this->decimal(10, 2)->comment('Текущая цена клика на поиске'),
            'competitors_bids' => $this->text()->comment('Массив минимальных ставок за все позиции в спецразмещении и в блоке гарантированных показов'),
        ]);

        $this->createTable('bid_ad_search_price', [
            'id' => $this->primaryKey()->unsigned(),
            'bid_id' => $this->bigInteger()->unsigned()->comment('Ставка'),
            'footer_block_price' => $this->decimal(10, 2)->comment('минимальная ставка за 4-ю позицию в гарантии (вход в блок гарантированных показов'),
            'footer_first_price' => $this->decimal(10, 2)->comment('минимальная ставка за 1-ю позицию в гарантии'),
            'premium_block_price' => $this->decimal(10, 2)->comment('минимальная ставка за 3-ю позицию в спецразмещении (вход в спецразмещение)'),
            'premium_first_price' => $this->decimal(10, 2)->comment('минимальная ставка за 1-ю позицию в спецразмещении')
        ]);

        $this->createTable('bid_context_coverage', [
            'id' => $this->primaryKey()->unsigned(),
            'bid_id' => $this->bigInteger()->unsigned()->comment('Ставка'),
            'probability' => $this->decimal(10, 2)->comment('Частота показа (доля аудитории) в сетях. Указывается в процентах от 0 до 100'),
            'price' => $this->decimal(10, 2)->comment('Ставка в сетях, при которой прогнозируется указанная частота показа')
        ]);

        $this->createTable('bid_auction_bid', [
            'id' => $this->primaryKey()->unsigned(),
            'bid_id' => $this->bigInteger()->unsigned()->comment('Ставка'),

            'spec1_bid' => $this->decimal(10, 2)->comment('Минимальная ставка спецразмещение 1 место'),
            'spec1_price' => $this->decimal(10, 2)->comment('Списываемая цена за спецразмещение 1 место'),
            'spec2_bid' => $this->decimal(10, 2)->comment('Минимальная ставка спецразмещение 2 место'),
            'spec2_price' => $this->decimal(10, 2)->comment('Списываемая цена за спецразмещение 2 место'),
            'spec3_bid' => $this->decimal(10, 2)->comment('Минимальная ставка спецразмещение 3 место'),
            'spec3_price' => $this->decimal(10, 2)->comment('Списываемая цена за спецразмещение 3 место'),

            'gar1_bid' => $this->decimal(10, 2)->comment('Минимальная ставка гарантированные показы 1 место'),
            'gar1_price' => $this->decimal(10, 2)->comment('Списываемая цена за гарантированные показы 1 место'),
            'gar2_bid' => $this->decimal(10, 2)->comment('Минимальная ставка гарантированные показы 2 место'),
            'gar2_price' => $this->decimal(10, 2)->comment('Списываемая цена за гарантированные показы 2 место'),
            'gar3_bid' => $this->decimal(10, 2)->comment('Минимальная ставка гарантированные показы 3 место'),
            'gar3_price' => $this->decimal(10, 2)->comment('Списываемая цена за гарантированные показы 3 место'),
            'gar4_bid' => $this->decimal(10, 2)->comment('Минимальная ставка гарантированные показы 4 место'),
            'gar4_price' => $this->decimal(10, 2)->comment('Списываемая цена за гарантированные показы 4 место'),
        ]);

        $this->addPrimaryKey(
            'pk_bid_yandex_bid',
            'bid_yandex_bid',
            'id'
        );

        $this->addPrimaryKey(
            'pk_bid_yandex_campaign',
            'bid_yandex_campaign',
            'id'
        );

        $this->addPrimaryKey(
            'pk_bid_yandex_ad_group',
            'bid_yandex_ad_group',
            'id'
        );

        $this->addPrimaryKey(
            'pk_bid_yandex_keyword',
            'bid_yandex_keyword',
            'id'
        );

        $this->alterColumn('bid_yandex_bid', 'id', $this->bigInteger()->unsigned().' NOT NULL AUTO_INCREMENT');

        $this->createIndex(
            'idx_bid_yandex_bid_campaign_id',
            'bid_yandex_bid',
            'campaign_id'
        );

        $this->createIndex(
            'idx_bid_yandex_bid_group_id',
            'bid_yandex_bid',
            'group_id'
        );

        $this->createIndex(
            'idx_bid_yandex_bid_keyword_id',
            'bid_yandex_bid',
            'keyword_id'
        );

        $this->createIndex(
            'idx_bid_yandex_ad_group_campaign_id',
            'bid_yandex_ad_group',
            'campaign_id'
        );

        $this->createIndex(
            'idx_bid_yandex_keyword_group_id',
            'bid_yandex_keyword',
            'group_id'
        );

        $this->createIndex(
            'idx_bid_yandex_keyword_campaign_id',
            'bid_yandex_keyword',
            'campaign_id'
        );

        $this->createIndex(
            'idx_bid_yandex_keyword_keyword',
            'bid_yandex_keyword',
            'keyword'
        );

        $this->addForeignKey(
            'fk_bid_ad_search_price_bid_id',
            'bid_ad_search_price',
            'bid_id',
            'bid_yandex_bid',
            'id',
            'cascade'
        );

        $this->addForeignKey(
            'fk_bid_context_coverage_bid_id',
            'bid_context_coverage',
            'bid_id',
            'bid_yandex_bid',
            'id',
            'cascade'
        );

        $this->addForeignKey(
            'fk_bid_auction_bid_bid_id',
            'bid_auction_bid',
            'bid_id',
            'bid_yandex_bid',
            'id',
            'cascade'
        );

        $this->addForeignKey(
            'fk_bid_yandex_ad_group_account_id',
            'bid_yandex_ad_group',
            'account_id',
            'bid_account',
            'id',
            'cascade'
        );

        $this->addForeignKey(
            'fk_bid_yandex_campaign_account_id',
            'bid_yandex_campaign',
            'account_id',
            'bid_account',
            'id',
            'cascade'
        );

        $this->addForeignKey(
            'fk_bid_yandex_keyword_account_id',
            'bid_yandex_keyword',
            'account_id',
            'bid_account',
            'id',
            'cascade'
        );

        $this->addForeignKey(
            'fk_bid_yandex_campaign_strategy_1',
            'bid_yandex_campaign',
            'strategy_1',
            'bid_strategy',
            'id',
            'set null'
        );

        $this->addForeignKey(
            'fk_bid_yandex_campaign_strategy_2',
            'bid_yandex_campaign',
            'strategy_2',
            'bid_strategy',
            'id',
            'set null'
        );

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

    public function down()
    {
        $this->dropTable('bid_ad_search_price');
        $this->dropTable('bid_context_coverage');
        $this->dropTable('bid_auction_bid');
        $this->dropTable('bid_yandex_campaign');
        $this->dropTable('bid_yandex_keyword');
        $this->dropTable('bid_yandex_ad_group');
        $this->dropTable('bid_yandex_bid');
        $this->dropTable('bid_account');
        $this->dropTable('bid_strategy');
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
