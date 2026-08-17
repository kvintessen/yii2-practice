<?php

declare(strict_types=1);

namespace app\services\Payment;

use app\models\Order;
use app\models\Payment;
use yii\web\Request;

interface PaymentGatewayInterface
{
    /** Stable machine key stored in payment.provider, e.g. 'yookassa'. */
    public function getCode(): string;

    /** Human-readable label for the checkout picker. */
    public function getLabel(): string;

    /**
     * Starts a payment with the provider for the given order and returns
     * where to redirect the buyer.
     */
    public function createPayment(Order $order, Payment $payment): PaymentInitResult;

    /**
     * Turns an inbound provider callback into an authoritative status.
     *
     * @throws InvalidWebhookException if the request can't be trusted (bad
     *     signature, malformed body, or the provider itself rejects the
     *     follow-up status lookup).
     */
    public function handleCallback(Request $request): PaymentCallbackResult;
}
