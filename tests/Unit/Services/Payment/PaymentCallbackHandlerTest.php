<?php

declare(strict_types=1);

namespace app\tests\Unit\Services\Payment;

use app\models\Order;
use app\models\Payment;
use app\services\Payment\FakeGateway;
use app\services\Payment\InitiatePaymentCommand;
use app\services\Payment\InitiatePaymentHandler;
use app\services\Payment\InvalidWebhookException;
use app\services\Payment\PaymentCallbackHandler;
use app\services\Payment\PaymentGatewayRegistry;
use app\tests\Support\Fixtures\UserFixture;
use yii\web\Request;

final class PaymentCallbackHandlerTest extends \Codeception\Test\Unit
{
    private PaymentCallbackHandler $_handler;
    private InitiatePaymentHandler $_initiateHandler;

    public function _fixtures(): array
    {
        return ['users' => UserFixture::class];
    }

    protected function _before()
    {
        $registry = new PaymentGatewayRegistry([new FakeGateway()]);
        $this->_handler = new PaymentCallbackHandler($registry);
        $this->_initiateHandler = new InitiatePaymentHandler($registry);
    }

    private function makePayment(): Payment
    {
        $order = new Order(['user_id' => 100, 'status' => Order::STATUS_NEW, 'total' => '30.00']);
        $order->save();

        return $this->_initiateHandler->handle(new InitiatePaymentCommand(100, $order->id, 'fake'));
    }

    private function callbackRequest(string $externalId, string $outcome): Request
    {
        return new Request(['bodyParams' => ['external_id' => $externalId, 'outcome' => $outcome]]);
    }

    public function testSuccessfulCallbackMarksPaymentAndOrderPaid()
    {
        $payment = $this->makePayment();

        $this->_handler->handle('fake', $this->callbackRequest($payment->external_id, 'succeeded'));

        $payment->refresh();
        verify($payment->status)->equals(Payment::STATUS_SUCCEEDED);

        $order = Order::findOne($payment->order_id);
        verify($order->status)->equals(Order::STATUS_PAID);
    }

    public function testCanceledCallbackLeavesOrderUnpaid()
    {
        $payment = $this->makePayment();

        $this->_handler->handle('fake', $this->callbackRequest($payment->external_id, 'canceled'));

        $payment->refresh();
        verify($payment->status)->equals(Payment::STATUS_CANCELED);

        $order = Order::findOne($payment->order_id);
        verify($order->status)->equals(Order::STATUS_NEW);
    }

    public function testRedeliveredCallbackIsIdempotent()
    {
        $payment = $this->makePayment();

        $this->_handler->handle('fake', $this->callbackRequest($payment->external_id, 'succeeded'));
        // A real provider may retry delivery; this must not error or re-apply side effects.
        $this->_handler->handle('fake', $this->callbackRequest($payment->external_id, 'succeeded'));

        $payment->refresh();
        verify($payment->status)->equals(Payment::STATUS_SUCCEEDED);
    }

    public function testCallbackForUnknownExternalIdIsIgnored()
    {
        $this->_handler->handle('fake', $this->callbackRequest('does-not-exist', 'succeeded'));

        verify(Payment::find()->count())->equals(0);
    }

    public function testMalformedCallbackThrowsInvalidWebhookException()
    {
        $payment = $this->makePayment();

        $this->expectException(InvalidWebhookException::class);
        $this->_handler->handle('fake', $this->callbackRequest($payment->external_id, 'not-a-real-status'));
    }
}
