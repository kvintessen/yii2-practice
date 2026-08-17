<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var app\models\Order $order */
/** @var app\services\Payment\PaymentGatewayInterface[] $gateways */

use yii\helpers\Html;

$this->title = 'Pay for order #' . $order->id;
$this->params['breadcrumbs'][] = ['label' => 'Order #' . $order->id, 'url' => ['/order/view', 'id' => $order->id]];
$this->params['breadcrumbs'][] = 'Pay';
?>
<div class="payment-pay">
    <h1><?= Html::encode($this->title) ?></h1>
    <p>Total: <strong><?= Html::encode($order->total) ?></strong></p>

    <?= Html::beginForm(['/payment/create', 'orderId' => $order->id], 'post', ['id' => 'payment-pay-form']) ?>
    <?php foreach ($gateways as $index => $gateway): ?>
        <?php $code = $gateway->getCode()->value; ?>
        <div class="form-check">
            <?= Html::radio(
                'provider',
                $index === 0,
                [
                    'id' => 'provider-' . $code,
                    'value' => $code,
                    'class' => 'form-check-input',
                ],
            ) ?>
            <?= Html::label(
                Html::encode($gateway->getLabel()),
                'provider-' . $code,
                ['class' => 'form-check-label'],
            ) ?>
        </div>
    <?php endforeach; ?>

    <?= Html::submitButton('Pay', ['class' => 'btn btn-primary btn-lg mt-3']) ?>
    <?= Html::endForm() ?>
</div>
