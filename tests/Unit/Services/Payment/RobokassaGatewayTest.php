<?php

declare(strict_types=1);

namespace app\tests\Unit\Services\Payment;

use app\models\Order;
use app\models\Payment;
use app\services\Payment\InvalidWebhookException;
use app\services\Payment\PaymentStatus;
use app\services\Payment\RobokassaGateway;
use yii\web\Request;

final class RobokassaGatewayTest extends \Codeception\Test\Unit
{
    private RobokassaGateway $_gateway;

    protected function _before()
    {
        $this->_gateway = new RobokassaGateway('demo_login', 'pass1', 'pass2', true);
    }

    public function testCreatePaymentBuildsACorrectlySignedRedirectUrl()
    {
        $order = new Order();
        $order->id = 42;
        $payment = new Payment(['amount' => '99.99']);

        $result = $this->_gateway->createPayment($order, $payment);

        verify(str_starts_with($result->confirmationUrl, 'https://auth.robokassa.ru/Merchant/Index.aspx?'))->true();

        parse_str((string) parse_url($result->confirmationUrl, PHP_URL_QUERY), $query);

        verify($query['MerchantLogin'])->equals('demo_login');
        verify($query['OutSum'])->equals('99.99');
        verify($query['InvId'])->equals($result->externalId);
        verify($query['IsTest'])->equals('1');
        verify($query['SignatureValue'])->equals(md5(sprintf(
            'demo_login:99.99:%s:pass1',
            $result->externalId,
        )));
    }

    public function testHandleCallbackAcceptsAValidSignature()
    {
        $invId = '123';
        $outSum = '10.00';
        $signature = md5("{$outSum}:{$invId}:pass2");

        $result = $this->_gateway->handleCallback($this->callbackRequest($outSum, $invId, $signature));

        verify($result->externalId)->equals($invId);
        verify($result->status)->equals(PaymentStatus::Succeeded);
        verify($this->_gateway->getCallbackAcknowledgement($result))->equals('OK123');
    }

    public function testHandleCallbackSignatureCheckIsCaseInsensitive()
    {
        $invId = '5';
        $outSum = '10.00';
        $signature = strtoupper(md5("{$outSum}:{$invId}:pass2"));

        $result = $this->_gateway->handleCallback($this->callbackRequest($outSum, $invId, $signature));

        verify($result->status)->equals(PaymentStatus::Succeeded);
    }

    public function testHandleCallbackRejectsAnInvalidSignature()
    {
        $this->expectException(InvalidWebhookException::class);
        $this->_gateway->handleCallback($this->callbackRequest('10.00', '123', 'not-the-real-signature'));
    }

    public function testHandleCallbackRejectsMissingFields()
    {
        $this->expectException(InvalidWebhookException::class);
        $this->_gateway->handleCallback(new Request(['bodyParams' => []]));
    }

    private function callbackRequest(string $outSum, string $invId, string $signature): Request
    {
        return new Request(['bodyParams' => [
            'OutSum' => $outSum,
            'InvId' => $invId,
            'SignatureValue' => $signature,
        ]]);
    }
}
