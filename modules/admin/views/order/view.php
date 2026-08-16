<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var app\models\Order $model */

use app\models\Order;
use yii\helpers\Html;
use yii\widgets\DetailView;

$this->title = 'Order #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Orders', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$statuses = [
    Order::STATUS_NEW => 'New',
    Order::STATUS_PAID => 'Paid',
    Order::STATUS_SHIPPED => 'Shipped',
    Order::STATUS_DONE => 'Done',
];
?>
<div class="order-view">
    <h1><?= Html::encode($this->title) ?></h1>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            [
                'label' => 'Customer',
                'value' => $model->user->username,
            ],
            'status',
            'total',
            'created_at:datetime',
            'updated_at:datetime',
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
            <?php foreach ($model->items as $item): ?>
                <tr>
                    <td><?= Html::encode($item->product_name) ?></td>
                    <td><?= Html::encode($item->unit_price) ?></td>
                    <td><?= $item->quantity ?></td>
                    <td><?= Html::encode($item->line_total) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?= Html::beginForm(['update-status', 'id' => $model->id], 'post', ['class' => 'row g-2 align-items-end']) ?>
    <div class="col-auto">
        <label class="form-label" for="order-status-select">Change status</label>
        <?= Html::dropDownList('status', $model->status, $statuses, ['id' => 'order-status-select', 'class' => 'form-select']) ?>
    </div>
    <div class="col-auto">
        <?= Html::submitButton('Update status', ['class' => 'btn btn-primary']) ?>
    </div>
    <?= Html::endForm() ?>
</div>
