<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var app\models\Order $order */
/** @var app\models\Payment|null $payment */

use yii\helpers\Html;

$this->title = 'Payment status';
$this->params['breadcrumbs'][] = ['label' => 'Order #' . $order->id, 'url' => ['/order/view', 'id' => $order->id]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="payment-return">
    <h1><?= Html::encode($this->title) ?></h1>

    <?php if ($payment === null): ?>
        <p>No payment attempt found for this order.</p>
    <?php else: ?>
        <p>
            Status: <strong><?= Html::encode($payment->status) ?></strong>
        </p>
        <?php if ($payment->status === \app\models\Payment::STATUS_PENDING): ?>
            <p class="text-muted">
                Confirmation is asynchronous — this page doesn't update itself. Refresh to check again,
                or watch the order status on <?= Html::a('the order page', ['/order/view', 'id' => $order->id]) ?>.
            </p>
        <?php endif; ?>

        <?php if (!YII_ENV_PROD && $payment->provider === 'fake' && $payment->status === \app\models\Payment::STATUS_PENDING): ?>
            <div class="card mt-4">
                <div class="card-body">
                    <h5 class="card-title">Dev tools: simulate the gateway's webhook</h5>
                    <p class="card-text text-muted">
                        The fake gateway has no real bank to call us back — post the same payload a real
                        provider's server would send to <code>payment/fake/callback</code>.
                    </p>
                    <?= Html::beginForm(
                        ['/payment/callback', 'provider' => 'fake'],
                        'post',
                        ['class' => 'd-inline', 'id' => 'simulate-success-form'],
                    ) ?>
                    <?= Html::hiddenInput('external_id', $payment->external_id) ?>
                    <?= Html::hiddenInput('outcome', 'succeeded') ?>
                    <?= Html::submitButton('Simulate success', ['class' => 'btn btn-success']) ?>
                    <?= Html::endForm() ?>
                    <?= Html::beginForm(
                        ['/payment/callback', 'provider' => 'fake'],
                        'post',
                        ['class' => 'd-inline', 'id' => 'simulate-failure-form'],
                    ) ?>
                    <?= Html::hiddenInput('external_id', $payment->external_id) ?>
                    <?= Html::hiddenInput('outcome', 'canceled') ?>
                    <?= Html::submitButton('Simulate failure', ['class' => 'btn btn-outline-danger']) ?>
                    <?= Html::endForm() ?>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
