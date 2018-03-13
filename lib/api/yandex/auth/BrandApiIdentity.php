<?php
/**
 * Created by PhpStorm.
 * User: den
 * Date: 15.10.16
 * Time: 23:53
 */

namespace app\lib\api\yandex\auth;

use app\lib\api\auth\ApiIdentityInterface;
use app\models\Account;
use app\models\BrandAccount;
use app\models\Shop;

/**
 * Class YandexToken
 * @package app\lib\api\yandex\auth
 */
class BrandApiIdentity implements ApiIdentityInterface
{
    /**
     * @var Shop
     */
    protected $shop;

    /**
     * @var int
     */
    protected $brandId;

    /**
     * YandexToken constructor.
     * @param Shop $shop
     * @param int $brandId
     */
    public function __construct(Shop $shop, $brandId = null)
    {
        $this->shop = $shop;
        $this->brandId = $brandId;
    }

    /**
     * @inheritDoc
     */
    public function getToken()
    {
        return $this->getAccount()->token;
    }

    /**
     * @inheritDoc
     */
    public function getAccount()
    {
        if (!$this->brandId) {
            return $this->shop->account;
        }

        return BrandAccount::getAccountByBrand($this->shop, $this->brandId);
    }
}
