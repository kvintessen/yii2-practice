<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var app\models\Order $order */

use app\models\Order;
use yii\helpers\Html;
use yii\widgets\DetailView;

$this->title = 'Order #' . $order->id;
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="order-view">
    <h1><?= Html::encode($this->title) ?></h1>

    <?php if ($order->status === Order::STATUS_NEW): ?>
        <?= Html::a('Pay now', ['/payment/pay', 'orderId' => $order->id], ['class' => 'btn btn-primary mb-3']) ?>
    <?php endif; ?>

    <?= DetailView::widget([
        'model' => $order,
        'attributes' => [
            'id',
            'status',
            'total',
            'created_at:datetime',
        ],
    ]) ?>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>Product</th>
                <th>Unit price</th>
                <th>Quantity</th>
                <th>Line total</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($order->items as $item): ?>
                <tr>
                    <td><?= Html::encode($item->product_name) ?></td>
                    <td><?= Html::encode($item->unit_price) ?></td>
                    <td><?= $item->quantity ?></td>
                    <td><?= Html::encode($item->line_total) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
