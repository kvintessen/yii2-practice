<?php

declare(strict_types=1);

namespace app\services\Payment;

use app\models\Order;
use app\models\Payment;
use yii\helpers\Url;
use yii\web\Request;

/**
 * A no-network stand-in for a real gateway. Registered alongside real
 * gateways so the checkout picker and the whole payment/create -> callback
 * flow are exercisable (Functional tests, manual dev testing) without real
 * provider credentials. Its "callback" isn't fired by a bank server — the
 * dev-mode panel on the return page (views/payment/return.php) posts to the
 * same payment/fake/callback endpoint a real webhook would hit, so it
 * exercises the exact same PaymentCallbackHandler code path as production
 * gateways.
 */
final class FakeGateway implements PaymentGatewayInterface
{
    public function getCode(): string
    {
        return 'fake';
    }

    public function getLabel(): string
    {
        return 'Test gateway (no real charge)';
    }

    public function createPayment(Order $order, Payment $payment): PaymentInitResult
    {
        $externalId = 'fake_' . bin2hex(random_bytes(8));

        return new PaymentInitResult(
            externalId: $externalId,
            confirmationUrl: Url::to(['/payment/return', 'provider' => $this->getCode(), 'orderId' => $order->id], true),
            rawPayload: json_encode(['id' => $externalId, 'order_id' => $order->id], JSON_THROW_ON_ERROR),
        );
    }

    public function handleCallback(Request $request): PaymentCallbackResult
    {
        $externalId = (string) $request->post('external_id');
        $outcome = (string) $request->post('outcome');

        if ($externalId === '') {
            throw new InvalidWebhookException('Missing external_id in fake callback.');
        }

        $status = PaymentStatus::tryFrom($outcome);

        if ($status === null) {
            throw new InvalidWebhookException(sprintf('Unknown outcome "%s" in fake callback.', $outcome));
        }

        return new PaymentCallbackResult(
            externalId: $externalId,
            status: $status,
            rawPayload: json_encode($request->post(), JSON_THROW_ON_ERROR),
        );
    }
}
