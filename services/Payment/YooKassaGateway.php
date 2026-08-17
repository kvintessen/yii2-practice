<?php

declare(strict_types=1);

namespace app\services\Payment;

use app\models\Order;
use app\models\Payment;
use Throwable;
use yii\base\Security;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\httpclient\Client;
use yii\web\Request;

/**
 * https://yookassa.ru/developers/api
 *
 * handleCallback() deliberately ignores the callback body's own status
 * field: YooKassa's basic notification setup has no signature scheme, so
 * anyone who finds the callback URL could otherwise POST a fake
 * "succeeded" event. Instead it takes only the payment id out of the body
 * and re-fetches the payment from the API with our own credentials — that
 * authenticated response, not the inbound POST, is the source of truth.
 */
final class YooKassaGateway implements PaymentGatewayInterface
{
    private const BASE_URL = 'https://api.yookassa.ru/v3';

    public function __construct(
        private readonly string $shopId,
        private readonly string $secretKey,
        private readonly Client $http,
        private readonly Security $security,
    ) {
    }

    public function getCode(): string
    {
        return 'yookassa';
    }

    public function getLabel(): string
    {
        return 'Bank card / SBP (YooKassa)';
    }

    public function createPayment(Order $order, Payment $payment): PaymentInitResult
    {
        $response = $this->request('POST', '/payments', [
            'amount' => [
                'value' => number_format((float) $payment->amount, 2, '.', ''),
                'currency' => $payment->currency,
            ],
            'confirmation' => [
                'type' => 'redirect',
                'return_url' => Url::to(
                    ['/payment/return', 'provider' => $this->getCode(), 'orderId' => $order->id],
                    true,
                ),
            ],
            'capture' => true,
            'description' => sprintf('Order #%d', $order->id),
            'metadata' => ['order_id' => $order->id],
        ], idempotent: true);

        $confirmationUrl = $response['confirmation']['confirmation_url'] ?? null;

        if (!isset($response['id']) || !is_string($confirmationUrl)) {
            throw new PaymentGatewayException('YooKassa did not return a payment id/confirmation_url.');
        }

        return new PaymentInitResult(
            externalId: (string) $response['id'],
            confirmationUrl: $confirmationUrl,
            rawPayload: Json::encode($response),
        );
    }

    public function handleCallback(Request $request): PaymentCallbackResult
    {
        $body = Json::decode($request->getRawBody() ?: '[]');
        $externalId = $body['object']['id'] ?? null;

        if (!is_string($externalId) || $externalId === '') {
            throw new InvalidWebhookException('YooKassa callback is missing object.id.');
        }

        // Source of truth is this authenticated lookup, not $body — see class docblock.
        try {
            $payment = $this->request('GET', '/payments/' . $externalId);
        } catch (PaymentGatewayException $e) {
            throw new InvalidWebhookException(
                'Could not verify the callback against the YooKassa API: ' . $e->getMessage(),
                previous: $e,
            );
        }

        $status = match ($payment['status'] ?? null) {
            'succeeded' => PaymentStatus::Succeeded,
            'canceled' => PaymentStatus::Canceled,
            'waiting_for_capture', 'pending' => PaymentStatus::Pending,
            default => throw new InvalidWebhookException(sprintf(
                'Unexpected YooKassa payment status "%s".',
                $payment['status'] ?? 'null',
            )),
        };

        return new PaymentCallbackResult(
            externalId: $externalId,
            status: $status,
            rawPayload: Json::encode($payment),
        );
    }

    /**
     * @param array<string, mixed> $body
     * @return array<string, mixed>
     */
    private function request(string $method, string $path, array $body = [], bool $idempotent = false): array
    {
        $request = $this->http->createRequest()
            ->setMethod($method)
            ->setUrl(self::BASE_URL . $path)
            ->addHeaders(['Authorization' => 'Basic ' . base64_encode($this->shopId . ':' . $this->secretKey)]);

        if ($idempotent) {
            $request->addHeaders(['Idempotence-Key' => $this->security->generateRandomString(32)]);
        }

        if ($body !== []) {
            $request->setFormat(Client::FORMAT_JSON)->setData($body);
        }

        try {
            $response = $request->send();
        } catch (Throwable $e) {
            throw new PaymentGatewayException('YooKassa API request failed: ' . $e->getMessage(), previous: $e);
        }

        if (!$response->isOk) {
            throw new PaymentGatewayException(sprintf('YooKassa API returned HTTP %s.', $response->statusCode));
        }

        return Json::decode($response->content);
    }
}
