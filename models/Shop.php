<?php

namespace app\models;

use app\helpers\AccountHelper;
use app\helpers\ArrayHelper;
use app\helpers\RemoteFileHelper;
use app\lib\variationStrategies\VariationStrategyFactory;
use app\models\search\ProductsSearch;
use yii\db\ActiveQuery;

/**
 * This is the model class for table "{{%shops}}".
 *
 * @property integer $id
 * @property string $name
 * @property string $brand_api_url
 * @property string $product_api_url
 * @property string $category_api_url
 * @property string $api_secret_key
 * @property string $external_strategy
 * @property string $schedule
 * @property string $remote_file_url
 * @property bool $is_import_schedule
 * @property string $strategy_factory
 * @property integer $account_id
 * @property string $href_template
 * @property boolean $is_autoupdate
 * @property string $schedule_autoupdate
 * @property bool $is_link_validation
 * @property string $variation_strategy
 * @property bool $is_shuffle_groups
 * @property string $shuffle_strategy
 *
 * @property Product[] $products
 * @property Sitelinks $sitelinks
 * @property Account $account
 */
class Shop extends BaseModel
{
    const EXTERNAL_STRATEGY_YML = 'yml';
    const EXTERNAL_STRATEGY_API = 'api';
    const EXTERNAL_STRATEGY_XLS = 'xls';

    const PARSE_STRATEGY_BRAND = 'brand';
    const PARSE_STRATEGY_DEFAULT = 'default';
    const PARSE_STRATEGY_DESCRIPTION = 'default_with_description';

    const SHUFFLE_STRATEGY_DEFAULT = 'shuffle_default';
    const SHUFFLE_STRATEGY_2 = 'shuffle_strategy_2';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%shop}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['brand_api_url', 'product_api_url', 'category_api_url'], 'required', 'when' => function (Shop $model) {
                return $model->external_strategy == Shop::EXTERNAL_STRATEGY_API;
            }],
            [['brand_api_url', 'product_api_url', 'api_secret_key', 'strategy_factory', 'variation_strategy'], 'string'],
            [['name'], 'string', 'max' => 50],
            [['is_import_schedule', 'is_autoupdate', 'is_link_validation', 'is_shuffle_groups'], 'boolean'],
            [['external_strategy', 'remote_file_url', 'href_template', 'shuffle_strategy'], 'string'],
            [['account_id'], 'integer'],
            [['account_id'], 'required'],
            ['schedule', 'validateSchedule', 'skipOnEmpty' => false, 'when' => function (Shop $model) {
                return $model->is_import_schedule;
            }],
            ['schedule_autoupdate', 'validateSchedule', 'skipOnEmpty' => false, 'when' => function (Shop $model) {
                return $model->is_autoupdate;
            }],
        ];
    }

    /**
     * @param $name
     * @param $params
     * @return bool
     */
    public function validateSchedule($name, $params)
    {
        $parts = preg_split('#\s+#', $this->$name);

        //расписание должны
        if (count($parts) != 5) {
            $this->addError($name, 'Неверный формат времени запуска');
            return false;
        }

        return true;
    }

    /**
     * @param string $name
     * @param array $params
     * @return bool
     */
    public function validateRemoteFile($name, $params)
    {
        if (!preg_match('#^(https?|ftp)://#', $this->remote_file_url)) {
            $this->addError($name, 'Неверно указан путь к файлу');
            return false;
        }

        try {
            $filePart = RemoteFileHelper::readPart($this->remote_file_url, 1024);
        } catch (\Exception $e) {
            $this->addError($name, $e->getMessage());
            return false;
        }


        if (!preg_match('#yml_catalog#', $filePart)) {
            $this->addError($name, 'Неверное содержимое файла');
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Название магазина',
            'brand_api_url' => 'Api работы с брендами',
            'product_api_url' => 'Api работы с товарами',
            'category_api_url' => 'Api работы с категориями',
            'api_secret_key' => 'Ключ доступа к api',
            'external_strategy' => 'Стратегия работы с товарами магазина',
            'remote_file_url' => 'Адрес файла для загрузки',
            'schedule' => 'Время запуска',
            'is_import_schedule' => 'Импорт по расписанию',
            'strategy_factory' => 'Стратегия парсинга',
            'account_id' => 'Аккаунт',
            'href_template' => 'Шаблон формирования ссылок',
            'is_autoupdate' => 'Использовать автообновление',
            'schedule_autoupdate' => 'Расписание автообновления',
            'is_link_validation' => 'Использовать проверку ссылок',
            'variation_strategy' => 'Стратегия генерации вариаций',
            'is_shuffle_groups' => 'Использовать мало показов',
            'shuffle_strategy' => 'Стратегия мало показов',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProducts()
    {
        return $this->hasMany(Product::className(), ['shop_id' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getSitelinks()
    {
        return $this->hasOne(Sitelinks::className(), ['shop_id' => 'id'])->orderBy('id desc');
    }

    /**
     * @return ActiveQuery
     */
    public function getAccount()
    {
        return $this->hasOne(Account::className(), ['id' => 'account_id']);
    }

    /**
     * @return array
     */
    public static function getAvailableExternalStrategies()
    {
        return [
            self::EXTERNAL_STRATEGY_API => 'Загрузка товаров через апи',
            self::EXTERNAL_STRATEGY_YML => 'Загрузка товаров yml',
            self::EXTERNAL_STRATEGY_XLS => 'Загрузка товаров xls'
        ];
    }

    /**
     * Стратегия работы с товарами посредством загрузки файлов
     *
     * @return bool
     */
    public function isFileLoadStrategy()
    {
        return in_array($this->external_strategy, [self::EXTERNAL_STRATEGY_XLS, self::EXTERNAL_STRATEGY_YML]);
    }

    /**
     * Возвращает список аккаунтов, которые учавствуют в обновлении магазина
     * и брендов
     *
     * @param int[] $brandIds
     * @return Account[]
     */
    public function getAccounts($brandIds = [])
    {
        return Account::find()
            ->andWhere(['id' => AccountHelper::getAccountIds($this, $brandIds)])
            ->all();
    }

    /**
     * @return array
     */
    public static function getVariationGenerationStrategies()
    {
        return [
            VariationStrategyFactory::DEFAULT_STRATEGY => 'По умолчанию',
            VariationStrategyFactory::WITHOUT_NUMBERS_STRATEGY => 'Исключаем без цифр',
            VariationStrategyFactory::MAIN_CATEGORY_WITH_MODEL => 'Без цифр + категория/модель',
        ];
    }

    /**
     * @return array
     */
    public static function getShuffleStrategies()
    {
        return [
            self::SHUFFLE_STRATEGY_DEFAULT => 'Мало показов',
            self::SHUFFLE_STRATEGY_2 => 'Мало показов 2 (бренд в кавычках, ставка 1 рубль)'
        ];
    }
}
