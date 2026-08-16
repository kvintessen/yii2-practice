<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var app\models\Cart $cart */

use yii\helpers\Html;

$this->title = 'Checkout';
$this->params['breadcrumbs'][] = ['label' => 'Cart', 'url' => ['/cart/index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="cart-checkout">
    <h1><?= Html::encode($this->title) ?></h1>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>Product</th>
                <th>Quantity</th>
                <th>Line total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($cart->items as $item): ?>
                <tr>
                    <td><?= Html::encode($item->product->name) ?></td>
                    <td><?= $item->quantity ?></td>
                    <td><?= number_format((float) $item->product->price * $item->quantity, 2) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="2" class="text-end">Total</th>
                <th><?= number_format($cart->getTotal(), 2) ?></th>
            </tr>
        </tfoot>
    </table>

    <?= Html::beginForm(['/cart/place-order'], 'post') ?>
    <?= Html::submitButton('Place order', ['class' => 'btn btn-primary btn-lg']) ?>
    <?= Html::endForm() ?>
</div>
