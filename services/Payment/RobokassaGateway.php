<?php

declare(strict_types=1);

namespace app\services\Payment;

use app\models\Order;
use app\models\Payment;
use yii\helpers\Json;
use yii\web\Request;

/**
 * https://docs.robokassa.ru
 *
 * Unlike YooKassa, Robokassa never hands back its own payment id — we
 * assign InvId ourselves when building the redirect link and Robokassa
 * just echoes it back on ResultURL, so createPayment() needs no network
 * call at all, unlike YooKassaGateway.
 *
 * ResultURL is only ever called for a *successful* payment — a declined or
 * abandoned attempt never reaches it (the buyer's browser lands on
 * FailURL instead, which isn't authoritative and isn't handled here), so
 * a signature that checks out always maps to PaymentStatus::Succeeded.
 */
final class RobokassaGateway implements PaymentGatewayInterface
{
    private const PAY_URL = 'https://auth.robokassa.ru/Merchant/Index.aspx';

    public function __construct(
        private readonly string $merchantLogin,
        private readonly string $password1,
        private readonly string $password2,
        private readonly bool $testMode,
    ) {
    }

    public function getCode(): PaymentProvider
    {
        return PaymentProvider::Robokassa;
    }

    public function getLabel(): string
    {
        return 'Bank card (Robokassa)';
    }

    public function createPayment(Order $order, Payment $payment): PaymentInitResult
    {
        // Robokassa's documented range for InvId is 1..PHP_INT_MAX (64-bit).
        // Picking it at random rather than reusing Payment::$id keeps
        // invoice numbers from leaking how many payments the shop has
        // processed.
        $invId = random_int(1, PHP_INT_MAX);
        $outSum = $payment->getFormattedAmount();

        $params = [
            'MerchantLogin' => $this->merchantLogin,
            'OutSum' => $outSum,
            'InvId' => $invId,
            'Description' => sprintf('Order #%d', $order->id),
            'SignatureValue' => $this->buildRequestSignature($outSum, $invId),
            'Culture' => 'ru',
        ];

        if ($this->testMode) {
            $params['IsTest'] = 1;
        }

        return new PaymentInitResult(
            externalId: (string) $invId,
            confirmationUrl: self::PAY_URL . '?' . http_build_query($params),
            rawPayload: Json::encode($params),
        );
    }

    public function handleCallback(Request $request): PaymentCallbackResult
    {
        $outSum = (string) ($request->post('OutSum') ?? $request->get('OutSum'));
        $invId = (string) ($request->post('InvId') ?? $request->get('InvId'));
        $signature = (string) ($request->post('SignatureValue') ?? $request->get('SignatureValue'));

        if ($outSum === '' || $invId === '' || $signature === '') {
            throw new InvalidWebhookException('Robokassa callback is missing OutSum/InvId/SignatureValue.');
        }

        $expected = $this->buildCallbackSignature($outSum, $invId);

        if (!hash_equals(strtolower($expected), strtolower($signature))) {
            throw new InvalidWebhookException('Robokassa callback signature mismatch.');
        }

        return new PaymentCallbackResult(
            externalId: $invId,
            status: PaymentStatus::Succeeded,
            rawPayload: Json::encode($request->post()),
        );
    }

    public function getCallbackAcknowledgement(PaymentCallbackResult $result): string
    {
        // Robokassa polls ResultURL until it sees exactly this — anything
        // else (including the generic "ok" other gateways here are happy
        // with) is treated as a failed delivery and retried.
        return 'OK' . $result->externalId;
    }

    /**
     * https://docs.robokassa.ru/ru/pay-interface — signs the outgoing
     * redirect so Robokassa can confirm the request came from us.
     */
    private function buildRequestSignature(string $outSum, int $invId): string
    {
        return md5(sprintf('%s:%s:%d:%s', $this->merchantLogin, $outSum, $invId, $this->password1));
    }

    /**
     * https://docs.robokassa.ru/ru/notifications-and-redirects — same shape
     * as the outgoing signature but keyed with Password #2 instead of #1,
     * which is what actually makes this trustworthy: forging a callback
     * requires knowing a secret Robokassa never sends anywhere in the
     * outgoing redirect.
     */
    private function buildCallbackSignature(string $outSum, string $invId): string
    {
        return md5(sprintf('%s:%s:%s', $outSum, $invId, $this->password2));
    }
}
