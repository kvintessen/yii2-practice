<?php

declare(strict_types=1);

namespace app\services\Payment;

use app\models\Order;
use app\models\Payment;
use DomainException;

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

        $payment = new Payment([
            'order_id' => $order->id,
            'provider' => $gateway->getCode(),
            'status' => Payment::STATUS_PENDING,
            'amount' => $order->total,
        ]);

        // Network round-trip to the provider happens before the row is ever
        // written, so a failed API call leaves nothing behind to clean up.
        $initResult = $gateway->createPayment($order, $payment);

        $payment->external_id = $initResult->externalId;
        $payment->confirmation_url = $initResult->confirmationUrl;
        $payment->raw_payload = $initResult->rawPayload;

        if (!$payment->save()) {
            throw new DomainException('Unable to store the payment.');
        }

        return $payment;
    }
}
