-- --------------------------------------------------------
-- Хост:                         127.0.0.1
-- Версия сервера:               5.7.18-0ubuntu0.16.04.1 - (Ubuntu)
-- ОС Сервера:                   Linux
-- HeidiSQL Версия:              9.3.0.4984
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Дамп структуры для таблица yd_admin_master.account
CREATE TABLE IF NOT EXISTS `account` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `token` varchar(255) DEFAULT NULL,
  `account_data` text,
  `account_type` varchar(255) DEFAULT NULL,
  `units` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Экспортируемые данные не выделены.


-- Дамп структуры для таблица yd_admin_master.ad
CREATE TABLE IF NOT EXISTS `ad` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `keywords` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Дата создания объявления',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT 'Дата обновления объявления',
  `generated_at` timestamp NULL DEFAULT NULL COMMENT 'Дата генерации объявления',
  `is_auto` tinyint(4) DEFAULT NULL,
  `is_deleted` tinyint(1) DEFAULT '0',
  `revision` int(11) DEFAULT '0',
  `is_require_verification` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk_ad_product` (`product_id`),
  CONSTRAINT `fk_ad_product` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Экспортируемые данные не выделены.


-- Дамп структуры для таблица yd_admin_master.ad_keyword
CREATE TABLE IF NOT EXISTS `ad_keyword` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `keyword` text COMMENT 'Ключевая фраза',
  `ad_id` int(11) DEFAULT NULL,
  `is_generated` tinyint(1) DEFAULT '0' COMMENT 'Объявление было сгенерировано',
  PRIMARY KEY (`id`),
  KEY `idx-ad_id-keyword` (`ad_id`),
  CONSTRAINT `fk-ad_keyword-ad_id` FOREIGN KEY (`ad_id`) REFERENCES `ad` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Экспортируемые данные не выделены.


-- Дамп структуры для таблица yd_admin_master.ad_template
CREATE TABLE IF NOT EXISTS `ad_template` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `shop_id` int(10) NOT NULL,
  `title` varchar(150) NOT NULL,
  `message` varchar(150) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `sort` int(11) DEFAULT NULL,
  `price_from` bigint(20) DEFAULT NULL,
  `price_to` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_templates_shops` (`shop_id`),
  CONSTRAINT `FK_templates_shops` FOREIGN KEY (`shop_id`) REFERENCES `shop` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Экспортируемые данные не выделены.


-- Дамп структуры для таблица yd_admin_master.ad_template_brand
CREATE TABLE IF NOT EXISTS `ad_template_brand` (
  `ad_template_id` int(11) NOT NULL,
  `brand_id` int(11) NOT NULL,
  PRIMARY KEY (`ad_template_id`,`brand_id`),
  CONSTRAINT `fk_ad_template_brand_ad_template` FOREIGN KEY (`ad_template_id`) REFERENCES `ad_template` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Экспортируемые данные не выделены.


-- Дамп структуры для таблица yd_admin_master.ad_template_campaign_template
CREATE TABLE IF NOT EXISTS `ad_template_campaign_template` (
  `ad_template_id` int(11) DEFAULT NULL,
  `campaign_template_id` int(11) DEFAULT NULL,
  UNIQUE KEY `idx_ad_template_campaign_template` (`ad_template_id`,`campaign_template_id`),
  KEY `fk_ad_template_campaign_template_ad_campaign_template_id` (`campaign_template_id`),
  CONSTRAINT `fk_ad_template_campaign_template_ad_campaign_template_id` FOREIGN KEY (`campaign_template_id`) REFERENCES `campaign_template` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ad_template_campaign_template_ad_template_id` FOREIGN KEY (`ad_template_id`) REFERENCES `ad_template` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=cp1251;

-- Экспортируемые данные не выделены.


-- Дамп структуры для таблица yd_admin_master.ad_template_category
CREATE TABLE IF NOT EXISTS `ad_template_category` (
  `ad_template_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  PRIMARY KEY (`ad_template_id`,`category_id`),
  CONSTRAINT `fk_ad_template_category_ad_template` FOREIGN KEY (`ad_template_id`) REFERENCES `ad_template` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Экспортируемые данные не выделены.


-- Дамп структуры для таблица yd_admin_master.ad_yandex_campaign
CREATE TABLE IF NOT EXISTS `ad_yandex_campaign` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ad_id` int(11) DEFAULT NULL,
  `yandex_campaign_id` int(11) DEFAULT NULL,
  `template_id` int(11) DEFAULT NULL,
  `yandex_ad_id` bigint(20) DEFAULT NULL,
  `yandex_adgroup_id` bigint(20) DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_published` tinyint(1) DEFAULT '0',
  `account_id` int(11) DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL COMMENT 'Результат операции, выполненной над объявлением',
  `state` varchar(255) DEFAULT NULL COMMENT 'Отражает текущее состояние объявления',
  `yandex_group_name` varchar(255) DEFAULT NULL COMMENT 'Название группы в директе',
  `ad_yandex_group_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_ad_yandex_campaign_ad` (`ad_id`),
  KEY `fk_ad_yandex_campaign_yandex_campaign` (`yandex_campaign_id`),
  KEY `fk_ad_yandex_campaign_template` (`template_id`),
  KEY `fk_ad_yandex_campaign_account_id_account` (`account_id`),
  KEY `fk-ad_yandex_campaign-ad_yandex_group_id` (`ad_yandex_group_id`),
  CONSTRAINT `fk-ad_yandex_campaign-ad_yandex_group_id` FOREIGN KEY (`ad_yandex_group_id`) REFERENCES `ad_yandex_group` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_ad_yandex_campaign_account_id_account` FOREIGN KEY (`account_id`) REFERENCES `account` (`id`),
  CONSTRAINT `fk_ad_yandex_campaign_ad` FOREIGN KEY (`ad_id`) REFERENCES `ad` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ad_yandex_campaign_template` FOREIGN KEY (`template_id`) REFERENCES `ad_template` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_ad_yandex_campaign_yandex_campaign` FOREIGN KEY (`yandex_campaign_id`) REFERENCES `yandex_campaign` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=cp1251;

-- Экспортируемые данные не выделены.


-- Дамп структуры для таблица yd_admin_master.ad_yandex_group
CREATE TABLE IF NOT EXISTS `ad_yandex_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `yandex_adgroup_id` varchar(255) DEFAULT NULL,
  `keywords_count` int(11) DEFAULT NULL,
  `status` varchar(255) DEFAULT 'draft' COMMENT 'Status',
  `serving_status` varchar(255) DEFAULT 'eligible' COMMENT 'Serving status',
  `ads_count` int(11) NOT NULL DEFAULT '0',
  `yandex_campaign_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx-ad_yandex_group-yandex_adgroup_id` (`yandex_adgroup_id`),
  KEY `idx-ad_yandex_group-serving_status` (`status`,`serving_status`),
  KEY `idx-ad_yandex_group-yandex_campaing_id-ads_count-keywords_count` (`yandex_campaign_id`,`serving_status`,`status`,`ads_count`,`keywords_count`),
  CONSTRAINT `fk-ad_yandex_group-yandex_campaing_id` FOREIGN KEY (`yandex_campaign_id`) REFERENCES `yandex_campaign` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Экспортируемые данные не выделены.


-- Дамп структуры для таблица yd_admin_master.bid_account
CREATE TABLE IF NOT EXISTS `bid_account` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `token` varchar(255) DEFAULT NULL,
  `settings` text,
  `type` varchar(255) DEFAULT NULL,
  `last_updated_at` timestamp NULL DEFAULT NULL,
  `max_click_price` int(11) DEFAULT NULL COMMENT 'Максимальная цена клика',
  `strategy_1` int(11) DEFAULT NULL COMMENT 'Основная стратегия',
  `strategy_2` int(11) DEFAULT NULL COMMENT 'Дополнительная стратегия',
  `units` varchar(255) DEFAULT NULL COMMENT 'Баллы',
  PRIMARY KEY (`id`),
  KEY `fk_bid_account_strategy_1` (`strategy_1`),
  KEY `fk_bid_account_strategy_2` (`strategy_2`),
  CONSTRAINT `fk_bid_account_strategy_1` FOREIGN KEY (`strategy_1`) REFERENCES `bid_strategy` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_bid_account_strategy_2` FOREIGN KEY (`strategy_2`) REFERENCES `bid_strategy` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Экспортируемые данные не выделены.


-- Дамп структуры для таблица yd_admin_master.bid_ad_search_price
CREATE TABLE IF NOT EXISTS `bid_ad_search_price` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `bid_id` bigint(20) unsigned DEFAULT NULL COMMENT 'Ставка',
  `footer_block_price` decimal(10,2) DEFAULT NULL COMMENT 'минимальная ставка за 4-ю позицию в гарантии (вход в блок гарантированных показов',
  `footer_first_price` decimal(10,2) DEFAULT NULL COMMENT 'минимальная ставка за 1-ю позицию в гарантии',
  `premium_block_price` decimal(10,2) DEFAULT NULL COMMENT 'минимальная ставка за 3-ю позицию в спецразмещении (вход в спецразмещение)',
  `premium_first_price` decimal(10,2) DEFAULT NULL COMMENT 'минимальная ставка за 1-ю позицию в спецразмещении',
  PRIMARY KEY (`id`),
  KEY `fk_bid_ad_search_price_bid_id` (`bid_id`),
  CONSTRAINT `fk_bid_ad_search_price_bid_id` FOREIGN KEY (`bid_id`) REFERENCES `bid_yandex_bid` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Экспортируемые данные не выделены.


-- Дамп структуры для таблица yd_admin_master.bid_auction_bid
CREATE TABLE IF NOT EXISTS `bid_auction_bid` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `bid_id` bigint(20) unsigned DEFAULT NULL COMMENT 'Ставка',
  `spec1_bid` decimal(10,2) DEFAULT NULL COMMENT 'Минимальная ставка спецразмещение 1 место',
  `spec1_price` decimal(10,2) DEFAULT NULL COMMENT 'Списываемая цена за спецразмещение 1 место',
  `spec2_bid` decimal(10,2) DEFAULT NULL COMMENT 'Минимальная ставка спецразмещение 2 место',
  `spec2_price` decimal(10,2) DEFAULT NULL COMMENT 'Списываемая цена за спецразмещение 2 место',
  `spec3_bid` decimal(10,2) DEFAULT NULL COMMENT 'Минимальная ставка спецразмещение 3 место',
  `spec3_price` decimal(10,2) DEFAULT NULL COMMENT 'Списываемая цена за спецразмещение 3 место',
  `gar1_bid` decimal(10,2) DEFAULT NULL COMMENT 'Минимальная ставка гарантированные показы 1 место',
  `gar1_price` decimal(10,2) DEFAULT NULL COMMENT 'Списываемая цена за гарантированные показы 1 место',
  `gar2_bid` decimal(10,2) DEFAULT NULL COMMENT 'Минимальная ставка гарантированные показы 2 место',
  `gar2_price` decimal(10,2) DEFAULT NULL COMMENT 'Списываемая цена за гарантированные показы 2 место',
  `gar3_bid` decimal(10,2) DEFAULT NULL COMMENT 'Минимальная ставка гарантированные показы 3 место',
  `gar3_price` decimal(10,2) DEFAULT NULL COMMENT 'Списываемая цена за гарантированные показы 3 место',
  `gar4_bid` decimal(10,2) DEFAULT NULL COMMENT 'Минимальная ставка гарантированные показы 4 место',
  `gar4_price` decimal(10,2) DEFAULT NULL COMMENT 'Списываемая цена за гарантированные показы 4 место',
  PRIMARY KEY (`id`),
  KEY `fk_bid_auction_bid_bid_id` (`bid_id`),
  CONSTRAINT `fk_bid_auction_bid_bid_id` FOREIGN KEY (`bid_id`) REFERENCES `bid_yandex_bid` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Экспортируемые данные не выделены.


-- Дамп структуры для таблица yd_admin_master.bid_context_coverage
CREATE TABLE IF NOT EXISTS `bid_context_coverage` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `bid_id` bigint(20) unsigned DEFAULT NULL COMMENT 'Ставка',
  `probability` decimal(10,2) DEFAULT NULL COMMENT 'Частота показа (доля аудитории) в сетях. Указывается в процентах от 0 до 100',
  `price` decimal(10,2) DEFAULT NULL COMMENT 'Ставка в сетях, при которой прогнозируется указанная частота показа',
  PRIMARY KEY (`id`),
  KEY `fk_bid_context_coverage_bid_id` (`bid_id`),
  CONSTRAINT `fk_bid_context_coverage_bid_id` FOREIGN KEY (`bid_id`) REFERENCES `bid_yandex_bid` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Экспортируемые данные не выделены.


-- Дамп структуры для таблица yd_admin_master.bid_strategy
CREATE TABLE IF NOT EXISTS `bid_strategy` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL COMMENT 'Заголовок стратегии',
  `delegee` varchar(255) DEFAULT NULL COMMENT 'Класс стратегии',
  `strategy` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Экспортируемые данные не выделены.


-- Дамп структуры для таблица yd_admin_master.bid_task
CREATE TABLE IF NOT EXISTS `bid_task` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'Время создания',
  `started_at` timestamp NULL DEFAULT NULL COMMENT 'Время запуска',
  `finished_at` timestamp NULL DEFAULT NULL COMMENT 'Время завершения',
  `account_id` int(11) DEFAULT NULL COMMENT 'Аккаунт',
  `status` varchar(255) DEFAULT NULL COMMENT 'Статус',
  `task` varchar(255) DEFAULT NULL COMMENT 'Задача',
  `context` text COMMENT 'Контекст',
  `message` text COMMENT 'Сообщение',
  `total_points` int(11) unsigned DEFAULT NULL COMMENT 'Потраченные баллы',
  PRIMARY KEY (`id`),
  KEY `fk_bid_task_account_id` (`account_id`),
  KEY `idx_account_id_status_task` (`task`,`status`),
  CONSTRAINT `fk_bid_task_account_id` FOREIGN KEY (`account_id`) REFERENCES `bid_account` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Экспортируемые данные не выделены.


-- Дамп структуры для таблица yd_admin_master.bid_task_log
CREATE TABLE IF NOT EXISTS `bid_task_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `task_id` int(11) unsigned DEFAULT NULL COMMENT 'Задача',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'Время создания',
  `level` varchar(255) DEFAULT NULL COMMENT 'Тип сообщения',
  `message` text COMMENT 'Сообщение',
  `context` text COMMENT 'Контекст',
  PRIMARY KEY (`id`),
  KEY `fk_bid_task_log_task_id` (`task_id`),
  CONSTRAINT `fk_bid_task_log_task_id` FOREIGN KEY (`task_id`) REFERENCES `bid_task` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Экспортируемые данные не выделены.


-- Дамп структуры для таблица yd_admin_master.bid_yandex_ad_group
CREATE TABLE IF NOT EXISTS `bid_yandex_ad_group` (
  `id` bigint(20) unsigned NOT NULL COMMENT 'Id группы',
  `campaign_id` bigint(20) DEFAULT NULL COMMENT 'Кампания',
  `account_id` int(11) DEFAULT NULL COMMENT 'Аккаунт',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL COMMENT 'Название группы',
  `status` varchar(255) DEFAULT NULL COMMENT 'Статус группы',
  `type` varchar(255) DEFAULT NULL COMMENT 'Тип группы объявлений',
  PRIMARY KEY (`id`),
  KEY `idx_bid_yandex_ad_group_campaign_id` (`campaign_id`),
  KEY `fk_bid_yandex_ad_group_account_id` (`account_id`),
  CONSTRAINT `fk_bid_yandex_ad_group_account_id` FOREIGN KEY (`account_id`) REFERENCES `bid_account` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Экспортируемые данные не выделены.


-- Дамп структуры для таблица yd_admin_master.bid_yandex_bid
CREATE TABLE IF NOT EXISTS `bid_yandex_bid` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `campaign_id` bigint(20) DEFAULT NULL COMMENT 'Кампания',
  `group_id` bigint(20) DEFAULT NULL COMMENT 'Группа объявления',
  `keyword_id` bigint(20) DEFAULT NULL COMMENT 'Ключевое слово',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `bid_serving_status` varchar(255) DEFAULT NULL COMMENT 'Статус возможности показов группы объявлений',
  `bid` decimal(10,2) DEFAULT NULL COMMENT 'Ставка на поиске',
  `context_bid` decimal(10,2) DEFAULT NULL COMMENT 'Ставка в сетях',
  `bid_min_search_price` decimal(10,2) DEFAULT NULL COMMENT 'Минимальная ставка, установленная для рекламодателя, при которой возможен показ на поиске',
  `bid_current_search_price` decimal(10,2) DEFAULT NULL COMMENT 'Текущая цена клика на поиске',
  `competitors_bids` text COMMENT 'Массив минимальных ставок за все позиции в спецразмещении и в блоке гарантированных показов',
  PRIMARY KEY (`id`),
  KEY `idx_bid_yandex_bid_campaign_id` (`campaign_id`),
  KEY `idx_bid_yandex_bid_group_id` (`group_id`),
  KEY `idx_bid_yandex_bid_keyword_id` (`keyword_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Экспортируемые данные не выделены.


-- Дамп структуры для таблица yd_admin_master.bid_yandex_campaign
CREATE TABLE IF NOT EXISTS `bid_yandex_campaign` (
  `id` bigint(20) unsigned NOT NULL COMMENT 'Id кампании',
  `account_id` int(11) DEFAULT NULL COMMENT 'Аккаунт',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL COMMENT 'Название кампании',
  `start_date` date DEFAULT NULL COMMENT 'Дата начала показа объявлений',
  `end_date` date DEFAULT NULL COMMENT 'Дата окончания показов объявлений',
  `status` varchar(255) DEFAULT NULL COMMENT 'Статус кампании',
  `state` varchar(255) DEFAULT NULL COMMENT 'Состояние кампании',
  `status_payment` varchar(255) DEFAULT NULL COMMENT 'Статус оплаты кампании',
  `status_clarification` varchar(255) DEFAULT NULL COMMENT 'Текстовое пояснение к статусу',
  `stat_clicks` bigint(20) DEFAULT NULL COMMENT 'Количество кликов за время существования кампании',
  `stat_impressions` bigint(20) DEFAULT NULL COMMENT 'Количество показов за время существования кампании',
  `currency` varchar(255) DEFAULT NULL COMMENT 'Валюта',
  `funds_mode` varchar(255) DEFAULT NULL COMMENT 'Тип финансовых показателей кампании',
  `funds_sum` decimal(10,2) DEFAULT NULL COMMENT 'Сумма средств, зачисленных на баланс кампании за время ее существования, в валюте рекламодателя, без НДС',
  `funds_balance` decimal(10,2) DEFAULT NULL COMMENT 'Текущий баланс кампании в валюте рекламодателя, без НДС',
  `funds_shared_refund` decimal(10,2) DEFAULT NULL COMMENT 'Сумма возврата средств за клики, признанные системой недобросовестными или ошибочными, без НДС',
  `funds_shared_spend` decimal(10,2) DEFAULT NULL COMMENT 'Сумма средств, израсходованных по данной кампании за все время ее существования, без НДС',
  `client_info` varchar(255) DEFAULT NULL COMMENT 'Название клиента',
  `daily_budget_amount` decimal(10,2) DEFAULT NULL COMMENT 'Дневной бюджет кампании в валюте рекламодателя',
  `daily_budget_mode` varchar(255) DEFAULT NULL COMMENT 'Тип дневного бюджета',
  `strategy_1` int(11) DEFAULT NULL COMMENT 'Стратегия 1',
  `strategy_2` int(11) DEFAULT NULL COMMENT 'Стратегия 2',
  `max_click_price` int(11) DEFAULT NULL COMMENT 'Максимальная цена клика',
  PRIMARY KEY (`id`),
  KEY `fk_bid_yandex_campaign_account_id` (`account_id`),
  KEY `fk_bid_yandex_campaign_strategy_1` (`strategy_1`),
  KEY `fk_bid_yandex_campaign_strategy_2` (`strategy_2`),
  CONSTRAINT `fk_bid_yandex_campaign_account_id` FOREIGN KEY (`account_id`) REFERENCES `bid_account` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_bid_yandex_campaign_strategy_1` FOREIGN KEY (`strategy_1`) REFERENCES `bid_strategy` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_bid_yandex_campaign_strategy_2` FOREIGN KEY (`strategy_2`) REFERENCES `bid_strategy` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Экспортируемые данные не выделены.


-- Дамп структуры для таблица yd_admin_master.bid_yandex_keyword
CREATE TABLE IF NOT EXISTS `bid_yandex_keyword` (
  `id` bigint(20) unsigned NOT NULL COMMENT 'Id',
  `keyword` varchar(255) DEFAULT NULL COMMENT 'Ключевое слово',
  `group_id` bigint(20) DEFAULT NULL COMMENT 'Группа объявления',
  `campaign_id` bigint(20) DEFAULT NULL COMMENT 'Кампания',
  `account_id` int(11) DEFAULT NULL COMMENT 'Аккаунт',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `bid` decimal(10,2) DEFAULT NULL COMMENT 'Ставка на поиске',
  `context_bid` decimal(10,2) DEFAULT NULL COMMENT 'Ставка в сетях',
  `state` varchar(255) DEFAULT NULL COMMENT 'Состояние ключевой фразы',
  `status` varchar(255) DEFAULT NULL COMMENT 'Статус ключевой фразы',
  `stat_search_clicks` bigint(20) DEFAULT NULL COMMENT 'Количество кликов по всем объявлениям группы, показанным по данной фразе',
  `stat_search_impressions` bigint(20) DEFAULT NULL COMMENT 'Количество показов всех объявлений группы по данной фразе',
  `stat_network_clicks` bigint(20) DEFAULT NULL COMMENT 'Количество кликов по всем объявлениям группы, показанным по данной фразе',
  `stat_network_impressions` bigint(20) DEFAULT NULL COMMENT 'Количество показов всех объявлений группы по данной фразе',
  `max_click_price` int(11) DEFAULT NULL COMMENT 'Максимальная цена клика',
  `strategy_1` int(11) DEFAULT NULL COMMENT 'Основная стратегия',
  `strategy_2` int(11) DEFAULT NULL COMMENT 'Доп стратегия',
  PRIMARY KEY (`id`),
  KEY `idx_bid_yandex_keyword_group_id` (`group_id`),
  KEY `idx_bid_yandex_keyword_campaign_id` (`campaign_id`),
  KEY `idx_bid_yandex_keyword_keyword` (`keyword`),
  KEY `fk_bid_yandex_keyword_account_id` (`account_id`),
  KEY `fk_bid_yandex_keyword_strategy_1` (`strategy_1`),
  KEY `fk_bid_yandex_keyword_strategy_2` (`strategy_2`),
  CONSTRAINT `fk_bid_yandex_keyword_account_id` FOREIGN KEY (`account_id`) REFERENCES `bid_account` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_bid_yandex_keyword_strategy_1` FOREIGN KEY (`strategy_1`) REFERENCES `bid_strategy` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_bid_yandex_keyword_strategy_2` FOREIGN KEY (`strategy_2`) REFERENCES `bid_strategy` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Экспортируемые данные не выделены.


-- Дамп структуры для таблица yd_admin_master.black_list
CREATE TABLE IF NOT EXISTS `black_list` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(1024) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `shop_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `black_list_name_type` (`name`(200),`type`),
  KEY `black_list_shop_id` (`shop_id`),
  CONSTRAINT `black_list_shop_id` FOREIGN KEY (`shop_id`) REFERENCES `shop` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Экспортируемые данные не выделены.


-- Дамп структуры для таблица yd_admin_master.brand_account
CREATE TABLE IF NOT EXISTS `brand_account` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `brand_id` int(11) DEFAULT NULL,
  `account_id` int(11) DEFAULT NULL,
  `shop_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_brand_account_account_id_account` (`account_id`),
  KEY `fk_brand_account_shop_id_shop` (`shop_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Экспортируемые данные не выделены.


-- Дамп структуры для таблица yd_admin_master.campaign_template
CREATE TABLE IF NOT EXISTS `campaign_template` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `shop_id` int(11) DEFAULT NULL,
  `regions` varchar(255) DEFAULT NULL,
  `negative_keywords` text,
  `text_campaign` text,
  PRIMARY KEY (`id`),
  KEY `fk_campaign_template_shop` (`shop_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Экспортируемые данные не выделены.


-- Дамп структуры для таблица yd_admin_master.campaign_template_brand
CREATE TABLE IF NOT EXISTS `campaign_template_brand` (
  `campaign_template_id` int(11) NOT NULL DEFAULT '0',
  `brand_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`campaign_template_id`,`brand_id`),
  CONSTRAINT `fk_campaign_template_brand_campaign_template` FOREIGN KEY (`campaign_template_id`) REFERENCES `campaign_template` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=cp1251;

-- Экспортируемые данные не выделены.


-- Дамп структуры для таблица yd_admin_master.external_brand
CREATE TABLE IF NOT EXISTS `external_brand` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `original_title` varchar(255) DEFAULT NULL COMMENT 'Исходное название бренда',
  `shop_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `outer_id` int(11) DEFAULT NULL,
  `is_deleted` tinyint(1) DEFAULT '0',
  `is_manual` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `external_brand_shop_id_fk` (`shop_id`),
  CONSTRAINT `external_brand_shop_id_fk` FOREIGN KEY (`shop_id`) REFERENCES `shop` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Экспортируемые данные не выделены.


-- Дамп структуры для таблица yd_admin_master.external_category
CREATE TABLE IF NOT EXISTS `external_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `outer_id` int(11) DEFAULT NULL,
  `shop_id` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `original_title` varchar(255) DEFAULT NULL COMMENT 'Исходное название категории',
  `parent_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `variations` varchar(1024) DEFAULT NULL,
  `is_manual` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `external_category_shop_id_fk` (`shop_id`),
  KEY `external_category_outer_id_shop_id_idx` (`outer_id`,`shop_id`),
  CONSTRAINT `external_category_shop_id_fk` FOREIGN KEY (`shop_id`) REFERENCES `shop` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Экспортируемые данные не выделены.


-- Дамп структуры для таблица yd_admin_master.external_product
CREATE TABLE IF NOT EXISTS `external_product` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `outer_id` varchar(255) DEFAULT NULL,
  `shop_id` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `model` varchar(255) DEFAULT NULL,
  `brand_id` int(11) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `is_available` tinyint(4) DEFAULT NULL,
  `picture` varchar(1024) DEFAULT NULL,
  `url` varchar(1024) DEFAULT NULL,
  `currency_id` varchar(11) DEFAULT NULL,
  `old_price` double DEFAULT NULL,
  `price` double DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `file_import_id` int(11) DEFAULT NULL,
  `original_title` varchar(255) DEFAULT NULL,
  `type_prefix` varchar(255) DEFAULT NULL,
  `is_manual` tinyint(1) DEFAULT '0',
  `is_url_available` tinyint(1) DEFAULT NULL,
  `available_check_at` timestamp NULL DEFAULT NULL,
  `is_generate_ad` tinyint(1) DEFAULT '1' COMMENT 'Генерировать объявления для товара',
  PRIMARY KEY (`id`),
  UNIQUE KEY `external_product_outer_id_shop_id_idx` (`outer_id`,`shop_id`),
  KEY `fk_external_product_category` (`category_id`),
  KEY `external_product_shop_id_idx` (`shop_id`),
  KEY `external_product_title_idx` (`title`),
  KEY `external_product_price_idx` (`price`),
  KEY `external_product_brand_idx` (`brand_id`),
  KEY `external_product_updated_idx` (`updated_at`),
  CONSTRAINT `external_product_shop_id_fk` FOREIGN KEY (`shop_id`) REFERENCES `shop` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_external_product_category` FOREIGN KEY (`category_id`) REFERENCES `external_category` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Экспортируемые данные не выделены.


-- Дамп структуры для таблица yd_admin_master.feed
CREATE TABLE IF NOT EXISTS `feed` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `domain` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Экспортируемые данные не выделены.


-- Дамп структуры для таблица yd_admin_master.feed_redirect
CREATE TABLE IF NOT EXISTS `feed_redirect` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `feed_id` int(11) DEFAULT NULL COMMENT 'Фид',
  `hash_url` varchar(255) DEFAULT NULL COMMENT 'Хэш урла',
  `target_url` varchar(2048) DEFAULT NULL COMMENT 'Урл, на который редиректить',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_feed_redirect_hash_url` (`hash_url`),
  KEY `fk_feed_redirect_feed_id` (`feed_id`),
  CONSTRAINT `fk_feed_redirect_feed_id` FOREIGN KEY (`feed_id`) REFERENCES `feed` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Экспортируемые данные не выделены.


-- Дамп структуры для таблица yd_admin_master.file_import
CREATE TABLE IF NOT EXISTS `file_import` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `original_filename` varchar(255) DEFAULT NULL,
  `filename` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `size` int(11) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `error_msg` varchar(255) DEFAULT NULL,
  `shop_id` int(11) DEFAULT NULL,
  `is_loaded` tinyint(1) DEFAULT '0',
  `company_name` varchar(255) DEFAULT NULL,
  `catalog_date` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Экспортируемые данные не выделены.


-- Дамп структуры для таблица yd_admin_master.file_import_log
CREATE TABLE IF NOT EXISTS `file_import_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `file_import_id` int(11) DEFAULT NULL,
  `title` varchar(250) DEFAULT NULL,
  `operation` varchar(50) DEFAULT NULL,
  `old_value` text,
  `new_value` text,
  `entity_type` varchar(20) DEFAULT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `file_import_lod_file_import_id_idx` (`file_import_id`),
  KEY `file_import_log_entity_idx` (`entity_id`,`entity_type`),
  CONSTRAINT `file_import_log_file_import_fk` FOREIGN KEY (`file_import_id`) REFERENCES `file_import` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Экспортируемые данные не выделены.


-- Дамп структуры для таблица yd_admin_master.forecast
CREATE TABLE IF NOT EXISTS `forecast` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shop_id` int(11) DEFAULT NULL,
  `brand_id` int(11) DEFAULT NULL,
  `points` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_forecast_shop_id` (`shop_id`),
  CONSTRAINT `fk_forecast_shop_id` FOREIGN KEY (`shop_id`) REFERENCES `shop` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Экспортируемые данные не выделены.


-- Дамп структуры для таблица yd_admin_master.generator_settings
CREATE TABLE IF NOT EXISTS `generator_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shop_id` int(11) NOT NULL,
  `price_from` double DEFAULT NULL,
  `price_to` double DEFAULT NULL,
  `brands` text,
  `filter` text,
  PRIMARY KEY (`id`),
  KEY `FK_generator_settings_shops` (`shop_id`),
  CONSTRAINT `FK_generator_settings_shops` FOREIGN KEY (`shop_id`) REFERENCES `shop` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Экспортируемые данные не выделены.


-- Дамп структуры для таблица yd_admin_master.migration
CREATE TABLE IF NOT EXISTS `migration` (
  `version` varchar(180) NOT NULL,
  `apply_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Экспортируемые данные не выделены.


-- Дамп структуры для таблица yd_admin_master.product
CREATE TABLE IF NOT EXISTS `product` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shop_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `brand_id` int(11) NOT NULL,
  `title` varchar(500) NOT NULL,
  `price` int(10) unsigned DEFAULT NULL,
  `manual_price` int(10) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  `is_available` tinyint(1) NOT NULL,
  `yandex_sitelink_id` bigint(20) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `is_duplicate` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `FK_products_shops` (`shop_id`),
  KEY `products_product_id_shop_id_index` (`product_id`,`shop_id`),
  CONSTRAINT `FK_products_shops` FOREIGN KEY (`shop_id`) REFERENCES `shop` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Экспортируемые данные не выделены.


-- Дамп структуры для таблица yd_admin_master.setting
CREATE TABLE IF NOT EXISTS `setting` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Экспортируемые данные не выделены.


-- Дамп структуры для таблица yd_admin_master.shop
CREATE TABLE IF NOT EXISTS `shop` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `brand_api_url` text,
  `product_api_url` text,
  `category_api_url` text,
  `api_secret_key` varchar(50) DEFAULT NULL,
  `yandex_application_id` varchar(50) DEFAULT NULL,
  `yandex_secret` varchar(50) DEFAULT NULL,
  `yandex_access_token` varchar(50) DEFAULT NULL,
  `external_strategy` varchar(255) DEFAULT NULL,
  `schedule` text,
  `remote_file_url` varchar(1024) DEFAULT NULL,
  `is_import_schedule` tinyint(1) DEFAULT '0' COMMENT 'Использовать импорт по расписанию',
  `strategy_factory` varchar(255) DEFAULT NULL,
  `account_id` int(11) DEFAULT NULL,
  `href_template` varchar(255) DEFAULT NULL,
  `is_autoupdate` tinyint(1) DEFAULT NULL COMMENT 'Использовать автообновление',
  `schedule_autoupdate` varchar(255) DEFAULT NULL COMMENT 'Расписание автообновления',
  `is_link_validation` tinyint(1) DEFAULT NULL COMMENT 'Использовать валидацию ссылок',
  `variation_strategy` varchar(255) DEFAULT NULL COMMENT 'Стратегия генерации вариаций',
  `is_shuffle_groups` tinyint(1) DEFAULT '0' COMMENT 'Функционал мало показов',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Экспортируемые данные не выделены.


-- Дамп структуры для таблица yd_admin_master.sitelink
CREATE TABLE IF NOT EXISTS `sitelink` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shop_id` int(11) DEFAULT NULL,
  `title` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK__shops` (`shop_id`),
  CONSTRAINT `FK__shops` FOREIGN KEY (`shop_id`) REFERENCES `shop` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Экспортируемые данные не выделены.


-- Дамп структуры для таблица yd_admin_master.sitelink_item
CREATE TABLE IF NOT EXISTS `sitelink_item` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sitelink_id` int(11) DEFAULT NULL,
  `title` varchar(30) DEFAULT NULL,
  `href` varchar(1024) DEFAULT NULL,
  `description` varchar(60) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sitelink_id` (`sitelink_id`),
  CONSTRAINT `FK_sitelinks_item_sitelinks` FOREIGN KEY (`sitelink_id`) REFERENCES `sitelink` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Экспортируемые данные не выделены.


-- Дамп структуры для таблица yd_admin_master.task_queue
CREATE TABLE IF NOT EXISTS `task_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shop_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `started_at` timestamp NULL DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `operation` varchar(50) DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `context` text,
  `error` text,
  `hash` varchar(32) DEFAULT NULL,
  `total_points` int(11) DEFAULT NULL,
  `log_file` varchar(1024) DEFAULT NULL,
  `info` varchar(1024) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `status` (`status`),
  KEY `FK_task_queue_shops` (`shop_id`),
  KEY `task_queue_hash_index` (`operation`,`hash`),
  CONSTRAINT `FK_task_queue_shops` FOREIGN KEY (`shop_id`) REFERENCES `shop` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Экспортируемые данные не выделены.


-- Дамп структуры для таблица yd_admin_master.user
CREATE TABLE IF NOT EXISTS `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `login` varchar(50) NOT NULL,
  `password_hash` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Экспортируемые данные не выделены.


-- Дамп структуры для таблица yd_admin_master.variation
CREATE TABLE IF NOT EXISTS `variation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shop_id` int(11) DEFAULT NULL,
  `entity_type` varchar(255) DEFAULT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `variation` text,
  PRIMARY KEY (`id`),
  KEY `fk_variation_shop_id` (`shop_id`),
  KEY `entity_id_type_ind` (`entity_id`,`entity_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Экспортируемые данные не выделены.


-- Дамп структуры для таблица yd_admin_master.variation_item
CREATE TABLE IF NOT EXISTS `variation_item` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `variation_id` int(11) DEFAULT NULL COMMENT 'Вариация',
  `value` varchar(1024) DEFAULT NULL COMMENT 'Текст вариации',
  `is_use_in_generation` tinyint(1) DEFAULT '1' COMMENT 'Использовать вариацию при генерации заголовков',
  PRIMARY KEY (`id`),
  KEY `fk_variation_item_variation` (`variation_id`),
  CONSTRAINT `fk_variation_item_variation` FOREIGN KEY (`variation_id`) REFERENCES `variation` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Экспортируемые данные не выделены.


-- Дамп структуры для таблица yd_admin_master.vcard
CREATE TABLE IF NOT EXISTS `vcard` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shop_id` int(11) DEFAULT NULL,
  `country` varchar(50) DEFAULT NULL,
  `city` varchar(55) DEFAULT NULL,
  `company_name` varchar(255) DEFAULT NULL,
  `work_time` varchar(255) DEFAULT NULL,
  `phone_country_code` varchar(255) DEFAULT NULL,
  `phone_city_code` varchar(255) DEFAULT NULL,
  `phone_number` varchar(255) DEFAULT NULL,
  `phone_extension` varchar(255) DEFAULT NULL,
  `street` varchar(55) DEFAULT NULL,
  `house` varchar(30) DEFAULT NULL,
  `building` varchar(10) DEFAULT NULL,
  `apartment` varchar(100) DEFAULT NULL,
  `extra_message` varchar(200) DEFAULT NULL,
  `contact_email` varchar(255) DEFAULT NULL,
  `ogrn` varchar(255) DEFAULT NULL,
  `contact_person` varchar(155) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `vcards_shop_fk` (`shop_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Экспортируемые данные не выделены.


-- Дамп структуры для таблица yd_admin_master.word_exception
CREATE TABLE IF NOT EXISTS `word_exception` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shop_id` int(11) DEFAULT NULL,
  `word` varchar(1024) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_word_exception_shop_id` (`shop_id`),
  CONSTRAINT `fk_word_exception_shop_id` FOREIGN KEY (`shop_id`) REFERENCES `shop` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Экспортируемые данные не выделены.


-- Дамп структуры для таблица yd_admin_master.yandex_campaign
CREATE TABLE IF NOT EXISTS `yandex_campaign` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shop_id` int(11) NOT NULL,
  `brand_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `yandex_id` bigint(20) NOT NULL,
  `products_count` int(11) NOT NULL DEFAULT '0',
  `yandex_vcard_id` bigint(20) DEFAULT NULL,
  `campaign_template_id` int(11) DEFAULT NULL,
  `account_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_yandex_campaign_shops` (`shop_id`),
  KEY `brand_id` (`brand_id`),
  KEY `fk_yandex_campaign_template` (`campaign_template_id`),
  KEY `fk_yandex_campaign_account_id_account` (`account_id`),
  CONSTRAINT `FK_yandex_campaign_shops` FOREIGN KEY (`shop_id`) REFERENCES `shop` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_yandex_campaign_account_id_account` FOREIGN KEY (`account_id`) REFERENCES `account` (`id`),
  CONSTRAINT `fk_yandex_campaign_template` FOREIGN KEY (`campaign_template_id`) REFERENCES `campaign_template` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Экспортируемые данные не выделены.


-- Дамп структуры для таблица yd_admin_master.yandex_oauth
CREATE TABLE IF NOT EXISTS `yandex_oauth` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `shop_id` int(11) NOT NULL,
  `access_token` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_oauth_users` (`user_id`),
  KEY `FK_oauth_shops` (`shop_id`),
  CONSTRAINT `FK_oauth_shops` FOREIGN KEY (`shop_id`) REFERENCES `shop` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `FK_oauth_users` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Экспортируемые данные не выделены.


-- Дамп структуры для таблица yd_admin_master.yandex_sitelink
CREATE TABLE IF NOT EXISTS `yandex_sitelink` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `yandex_id` bigint(20) DEFAULT NULL COMMENT 'Уникальный идентификатор в директе',
  `shop_id` int(11) DEFAULT NULL COMMENT 'Магазин',
  `account_id` int(11) DEFAULT NULL COMMENT 'Аккаунт',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Экспортируемые данные не выделены.


-- Дамп структуры для таблица yd_admin_master.yandex_update_log
CREATE TABLE IF NOT EXISTS `yandex_update_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `task_id` int(11) NOT NULL,
  `shop_id` int(11) NOT NULL,
  `entity_type` varchar(50) NOT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `operation` varchar(50) NOT NULL,
  `status` varchar(50) NOT NULL,
  `message` varchar(2048) DEFAULT NULL,
  `points` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_yandex_update_log_shops` (`shop_id`),
  KEY `task_id` (`task_id`),
  CONSTRAINT `FK_yandex_update_log_shops` FOREIGN KEY (`shop_id`) REFERENCES `shop` (`id`) ON DELETE CASCADE,
  CONSTRAINT `FK_yandex_update_log_task_queue` FOREIGN KEY (`task_id`) REFERENCES `task_queue` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Экспортируемые данные не выделены.
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
