<?php

use app\helpers\ArrayHelper;
use app\models\Account;
use app\models\Shop;
use yii\bootstrap\Html;
use app\lib\dto\Brand;
use yii\web\View;

/**
 * @var Brand[] $brands
 * @var Account[] $accounts
 * @var View $this
 * @var array $brandsByAccount
 * @var Shop $shop
 */

?>

<h3>Аккаунты</h3>
<div class="row accounts-list-container" data-shop-id="<?=$shop->id?>">
    <div class="col-sm-9">
        <div class="form-group">
            <div style="height: 200px; overflow: hidden; overflow-y: scroll;">
                <div class="accounts-list">
                    <div class="checkbox">
                        <label>
                            <?= Html::checkbox('accountIds[]', true, ['value' => '0', 'class' => 'account-checkbox all-account-checkbox'])?>
                            <span class="account-title">Все</span>
                        </label>
                    </div>
                    <?foreach ($accounts as $account):?>
                        <?$accountBrands = ArrayHelper::getValue($brandsByAccount, $account->id, [])?>
                        <div class="checkbox">
                            <label>
                                <?= Html::checkbox('accountIds[]', true, ['value' => $account->id, 'class' => 'account-checkbox', 'data-brand-ids' => ArrayHelper::getColumn($accountBrands, 'id')])?>
                                <span class="account-title"><?= $account->title ?>
                                    <span style="font-size: 0.8em; color: dimgray;"> (<?=implode(', ', ArrayHelper::getColumn($accountBrands, 'title'))?>)</span>
                                </span>
                            </label>
                        </div>
                    <?endforeach;?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-3">
        <?= Html::button('Обновить', ['class' => 'btn btn-primary yandex-update'])?>
        <p>Итого: <span class="total-points"></span> </p>
    </div>
</div>

<h3>Бренды</h3>
<div class="row brands-list-container" data-shop-id="<?=$shop->id?>">
    <div class="col-sm-9">
        <div class="form-group">
            <div style="height: 500px; overflow: hidden; overflow-y: scroll;">
                <div class="brands-list">
                    <div class="checkbox">
                        <label>
                            <?= Html::checkbox('brandIds[]', true, ['value' => '0', 'class' => 'brand-checkbox all-brand-checkbox'])?>
                            <span class="brand-title">Все</span>
                        </label>
                    </div>
                    <?foreach ($brands as $brand):?>
                        <div class="checkbox">
                            <label>
                                <?= Html::checkbox('brandIds[]', true, ['value' => $brand->id, 'class' => 'brand-checkbox', 'data-brand-id' => $brand->id])?>
                                <span class="brand-title"><?= $brand->title ?> <i style="color: grey">(<?=ArrayHelper::getValue($brandAccountsMap, $brand->id, $shop->account->title)?>)</i> (<?= !is_null($brand->points) ? Html::tag('span', $brand->points, ['class' => 'points-value']) : 'Неизвестно'?>)</span>
                            </label>
                        </div>
                    <?endforeach;?>
                </div>
            </div>
        </div>
    </div>
</div>