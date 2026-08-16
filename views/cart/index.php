<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var app\models\Cart|null $cart */

use yii\helpers\Html;

$this->title = 'Your Cart';
$this->params['breadcrumbs'][] = $this->title;

$items = $cart?->items ?? [];
?>
<div class="cart-index">
    <h1><?= Html::encode($this->title) ?></h1>

    <?php if ($items === []): ?>
        <p>Your cart is empty. <?= Html::a('Browse the catalog', ['/catalog/index']) ?>.</p>
    <?php else: ?>
        <div class="table-responsive">
        <table class="table table-striped align-middle">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Line total</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td>
                            <?= Html::a(Html::encode($item->product->name), ['/catalog/view', 'id' => $item->product_id]) ?>
                        </td>
                        <td><?= Html::encode($item->product->price) ?></td>
                        <td style="max-width: 160px;">
                            <?= Html::beginForm(['/cart/update-quantity'], 'post', ['class' => 'd-flex gap-1']) ?>
                            <?= Html::hiddenInput('item_id', $item->id) ?>
                            <input
                                type="number"
                                name="quantity"
                                value="<?= $item->quantity ?>"
                                min="1"
                                max="<?= $item->product->stock ?>"
                                class="form-control form-control-sm"
                            >
                            <?= Html::submitButton('Update', ['class' => 'btn btn-sm btn-outline-secondary']) ?>
                            <?= Html::endForm() ?>
                        </td>
                        <td><?= number_format((float) $item->product->price * $item->quantity, 2) ?></td>
                        <td>
                            <?= Html::beginForm(['/cart/remove'], 'post') ?>
                            <?= Html::hiddenInput('item_id', $item->id) ?>
                            <?= Html::submitButton('Remove', ['class' => 'btn btn-sm btn-outline-danger']) ?>
                            <?= Html::endForm() ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="3" class="text-end">Total</th>
                    <th><?= number_format($cart->getTotal(), 2) ?></th>
                    <th></th>
                </tr>
            </tfoot>
        </table>
        </div>

        <?= Html::a('Proceed to checkout', ['/cart/checkout'], ['class' => 'btn btn-primary']) ?>
    <?php endif; ?>
</div>
