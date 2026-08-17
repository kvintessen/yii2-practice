<?php

declare(strict_types=1);

namespace app\tests\Unit\Services\Payment;

use app\models\Order;
use app\models\Payment;
use app\services\Payment\FakeGateway;
use app\services\Payment\InitiatePaymentCommand;
use app\services\Payment\InitiatePaymentHandler;
use app\services\Payment\PaymentGatewayRegistry;
use app\tests\Support\Fixtures\UserFixture;
use DomainException;
use OutOfBoundsException;

final class InitiatePaymentHandlerTest extends \Codeception\Test\Unit
{
    private InitiatePaymentHandler $_handler;

    public function _fixtures(): array
    {
        return ['users' => UserFixture::class];
    }

    protected function _before()
    {
        $this->_handler = new InitiatePaymentHandler(new PaymentGatewayRegistry([new FakeGateway()]));
    }

    private function makeOrder(int $userId, string $status = Order::STATUS_NEW, string $total = '30.00'): Order
    {
        $order = new Order(['user_id' => $userId, 'status' => $status, 'total' => $total]);
        $order->save();

        return $order;
    }

    public function testInitiatingPaymentCreatesAPendingPaymentRow()
    {
        $order = $this->makeOrder(100);

        $payment = $this->_handler->handle(new InitiatePaymentCommand(100, $order->id, 'fake'));

        verify($payment->status)->equals(Payment::STATUS_PENDING);
        verify($payment->provider)->equals('fake');
        verify($payment->order_id)->equals($order->id);
        verify((float) $payment->amount)->equals(30.0);
        verify($payment->external_id)->notNull();
        verify($payment->confirmation_url)->notNull();
    }

    public function testCannotInitiatePaymentForAnotherUsersOrder()
    {
        $order = $this->makeOrder(100);

        $this->expectException(DomainException::class);
        $this->_handler->handle(new InitiatePaymentCommand(101, $order->id, 'fake'));
    }

    public function testCannotInitiatePaymentForANonNewOrder()
    {
        $order = $this->makeOrder(100, Order::STATUS_PAID);

        $this->expectException(DomainException::class);
        $this->_handler->handle(new InitiatePaymentCommand(100, $order->id, 'fake'));
    }

    public function testUnknownProviderThrows()
    {
        $order = $this->makeOrder(100);

        $this->expectException(OutOfBoundsException::class);
        $this->_handler->handle(new InitiatePaymentCommand(100, $order->id, 'unknown-provider'));
    }
}
