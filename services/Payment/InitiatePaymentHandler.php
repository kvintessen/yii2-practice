<?php

declare(strict_types=1);

namespace app\services\Payment;

use app\models\Order;
use app\models\Payment;
use DomainException;
use yii\db\IntegrityException;

final class InitiatePaymentHandler
{
    public function __construct(
        private readonly PaymentGatewayRegistry $registry,
    ) {
    }

    public function handle(InitiatePaymentCommand $command): Payment
    {
        $order = Order::findOne($command->orderId);

        if ($order === null || $order->user_id !== $command->userId) {
            throw new DomainException('Order not found for this user.');
        }

        if ($order->status !== Order::STATUS_NEW) {
            throw new DomainException('This order is not awaiting payment.');
        }

        $gateway = $this->registry->get($command->providerCode);

        $pending = Payment::find()
            ->where(['order_id' => $order->id, 'status' => Payment::STATUS_PENDING])
            ->one();

        if ($pending !== null) {
            if ($pending->provider === $gateway->getCode()->value) {
                // Same method clicked again (double-click, back button) —
                // reuse the in-flight attempt instead of opening a second
                // one with the provider.
                return $pending;
            }

            // Switching methods: at most one pending attempt per order is
            // allowed (enforced below by a DB constraint too), so the old
            // one is superseded rather than left dangling forever.
            $pending->status = Payment::STATUS_CANCELED;
            $pending->save(false, ['status', 'updated_at']);
        }

        $payment = new Payment([
            'order_id' => $order->id,
            'provider' => $gateway->getCode()->value,
            'status' => Payment::STATUS_PENDING,
            'amount' => $order->total,
            // Payment::rules() also defaults this to 'RUB', but that's a
            // *validation-time* default — validate()/save() haven't run
            // yet at this point (see the comment below), so without this
            // the gateway would read a still-null currency.
            'currency' => 'RUB',
        ]);

        // Network round-trip to the provider happens before the row is ever
        // written, so a failed API call leaves nothing behind to clean up.
        $initResult = $gateway->createPayment($order, $payment);

        $payment->external_id = $initResult->externalId;
        $payment->confirmation_url = $initResult->confirmationUrl;
        $payment->raw_payload = $initResult->rawPayload;

        try {
            $saved = $payment->save();
        } catch (IntegrityException) {
            // Two concurrent requests both passed the check above and
            // raced to insert — the DB's partial unique index on
            // (order_id) WHERE status = 'pending' is the actual guard.
            throw new DomainException(
                'A payment is already being processed for this order. Please wait a moment and try again.',
            );
        }

        if (!$saved) {
            throw new DomainException('Unable to store the payment.');
        }

        return $payment;
    }
}
