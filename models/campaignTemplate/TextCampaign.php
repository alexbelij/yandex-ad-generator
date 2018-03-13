<?php

namespace app\models\campaignTemplate;

use yii\base\Model;

/**
 * Модель текстовой кампании
 *
 * Class TextCampaign
 * @package app\models\campaignTemplate
 * @author Denis Dyadyun <sysadm85@gmail.com>
 */
class TextCampaign extends Model
{
    const STRATEGY_SEARCH_HIGHEST_POSITION = 'HIGHEST_POSITION';
    const STRATEGY_SEARCH_LOWEST_COST = 'LOWEST_COST';
    const STRATEGY_SEARCH_LOWEST_COST_PREMIUM = 'LOWEST_COST_PREMIUM';
    const STRATEGY_SEARCH_LOWEST_COST_GUARANTEE = 'LOWEST_COST_GUARANTEE';
    const STRATEGY_SEARCH_IMPRESSIONS_BELOW_SEARCH = 'IMPRESSIONS_BELOW_SEARCH';
    const STRATEGY_SEARCH_SERVING_OFF = 'SERVING_OFF';

    const STRATEGY_NETWORK_NETWORK_DEFAULT = 'NETWORK_DEFAULT';
    const STRATEGY_NETWORK_SERVING_OFF = 'SERVING_OFF';
    const STRATEGY_NETWORK_MAXIMUM_COVERAGE = 'MAXIMUM_COVERAGE';

    const SETTING_ADD_METRICA_TAG = 'ADD_METRICA_TAG';
    const SETTING_ADD_OPENSTAT_TAG = 'ADD_OPENSTAT_TAG';
    const SETTING_ADD_TO_FAVORITES = 'ADD_TO_FAVORITES';
    const SETTING_ENABLE_AREA_OF_INTEREST_TARGETING = 'ENABLE_AREA_OF_INTEREST_TARGETING';
    const SETTING_ENABLE_AUTOFOCUS = 'ENABLE_AUTOFOCUS';
    const SETTING_ENABLE_BEHAVIORAL_TARGETING = 'ENABLE_BEHAVIORAL_TARGETING';
    const SETTING_ENABLE_EXTENDED_AD_TITLE = 'ENABLE_EXTENDED_AD_TITLE';
    const SETTING_ENABLE_RELATED_KEYWORDS = 'ENABLE_RELATED_KEYWORDS';
    const SETTING_ENABLE_SITE_MONITORING = 'ENABLE_SITE_MONITORING';
    const SETTING_EXCLUDE_PAUSED_COMPETING_ADS = 'EXCLUDE_PAUSED_COMPETING_ADS';
    const SETTING_MAINTAIN_NETWORK_CPC = 'MAINTAIN_NETWORK_CPC';
    const SETTING_REQUIRE_SERVICING = 'REQUIRE_SERVICING';

    /**
     * @var string
     */
    public $biddingStrategySearchType;

    /**
     * @var string
     */
    public $biddingStrategyNetworkType;

    /**
     * @var string
     */
    public $counterIds;

    /**
     * @var array
     */
    public $settings = [];

    /**
     * @inheritDoc
     */
    public function rules()
    {
        return [
            [['biddingStrategySearchType', 'biddingStrategyNetworkType'], 'string'],
            [['settings', 'counterIds'], 'safe'],
        ];
    }

    /**
     * @inheritDoc
     */
    public function attributeLabels()
    {
        return [
            'biddingStrategySearchType' => 'Стратегия на поиске',
            'biddingStrategyNetworkType' => 'Допустимые стратегии на тематических площадках',
            'counterIds' => 'Идентификаторы счетчиков Яндекс.Метрики, не более 5 элементов (через запятую)',
        ];
    }

    /**
     * Стратегии показа при поиске
     * @return array
     */
    public static function getSearchStrategyList()
    {
        return [
            self::STRATEGY_SEARCH_HIGHEST_POSITION => 'Наивысшая доступная позиция',
            self::STRATEGY_SEARCH_LOWEST_COST => 'Показ в блоке по минимальной цене (включить в спецразмещении и «гарантии»)',
            self::STRATEGY_SEARCH_LOWEST_COST_PREMIUM => 'Показ в блоке по минимальной цене (включить в спецразмещении)',
            self::STRATEGY_SEARCH_LOWEST_COST_GUARANTEE => 'Показ под результатами поиска (в «гарантии» по минимальной цене)',
            self::STRATEGY_SEARCH_IMPRESSIONS_BELOW_SEARCH => 'Показ под результатами поиска (на наивысшей доступной позиции)',
            self::STRATEGY_SEARCH_SERVING_OFF => 'Показы отключены'
        ];
    }

    /**
     * Стратегии показа на тематических площадках
     * @return array
     */
    public function getNetworkStrategyList()
    {
        if ($this->biddingStrategySearchType == self::STRATEGY_SEARCH_SERVING_OFF) {
            return [
                self::STRATEGY_NETWORK_MAXIMUM_COVERAGE => '«Максимальный доступный охват»'
            ];
        }

        return [
//            self::STRATEGY_NETWORK_NETWORK_DEFAULT => 'Процент от цены на поиске',
//            self::STRATEGY_NETWORK_MAXIMUM_COVERAGE => '«Максимальный доступный охват»',
            self::STRATEGY_NETWORK_SERVING_OFF => 'Показы отключены'
        ];
    }

    /**
     * Список настроек
     *
     * @return array
     */
    public static function getSettingsList()
    {
        return [
            self::SETTING_ADD_METRICA_TAG => 'Aвтоматически добавлять в ссылку объявления метку yclid с уникальным номером клика',
            self::SETTING_ADD_OPENSTAT_TAG => 'При переходе на сайт рекламодателя добавлять к URL метку в формате OpenStat',
            self::SETTING_ADD_TO_FAVORITES => 'Добавить кампанию в самые важные для применения фильтра в веб-интерфейсе по этому признаку',
            self::SETTING_ENABLE_AREA_OF_INTEREST_TARGETING => 'Включить Расширенный географический таргетинг',
            self::SETTING_ENABLE_AUTOFOCUS => 'Включить Автофокус',
            self::SETTING_ENABLE_BEHAVIORAL_TARGETING => 'Включить поведенческий таргетинг',
            self::SETTING_ENABLE_EXTENDED_AD_TITLE => 'Включить подстановку части текста объявления в заголовок',
            self::SETTING_ENABLE_RELATED_KEYWORDS => 'Включить Авторасширение фраз',
            self::SETTING_ENABLE_SITE_MONITORING => 'Останавливать показы при недоступности сайта рекламодателя',
            self::SETTING_EXCLUDE_PAUSED_COMPETING_ADS => 'Рассчитывать минимальные ставки за позиции показа без учета ставок в объявлениях конкурентов, остановленных в соответствии с временным таргетингом',
            self::SETTING_MAINTAIN_NETWORK_CPC => 'Удерживать среднюю цену клика на тематических площадках ниже средней цены на поиске',
            self::SETTING_REQUIRE_SERVICING => 'Перевести кампанию на обслуживание персональным менеджером'
        ];
    }
}
