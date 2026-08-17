<?php

declare(strict_types=1);

namespace app\tests\Unit\Services\Payment;

use app\models\Order;
use app\models\Payment;
use app\services\Payment\FakeGateway;
use app\services\Payment\InitiatePaymentCommand;
use app\services\Payment\InitiatePaymentHandler;
use app\services\Payment\PaymentCallbackResult;
use app\services\Payment\PaymentGatewayInterface;
use app\services\Payment\PaymentGatewayRegistry;
use app\services\Payment\PaymentInitResult;
use app\services\Payment\PaymentProvider;
use app\tests\Support\Fixtures\UserFixture;
use DomainException;
use OutOfBoundsException;
use RuntimeException;
use yii\web\Request;

final class InitiatePaymentHandlerTest extends \Codeception\Test\Unit
{
    private InitiatePaymentHandler $_handler;

    public function _fixtures(): array
    {
        return ['users' => UserFixture::class];
    }

    protected function _before()
    {
        $this->_handler = new InitiatePaymentHandler(
            new PaymentGatewayRegistry([new FakeGateway(), $this->secondFakeGateway()]),
        );
    }

    /**
     * A second, distinctly-coded gateway — needed to exercise "switching
     * providers" without depending on a second real implementation. Reuses
     * the Robokassa code purely as a stand-in identity; nothing about its
     * behavior below is actually Robokassa-specific.
     */
    private function secondFakeGateway(): PaymentGatewayInterface
    {
        return new class implements PaymentGatewayInterface {
            public function getCode(): PaymentProvider
            {
                return PaymentProvider::Robokassa;
            }

            public function getLabel(): string
            {
                return 'Second test gateway';
            }

            public function createPayment(Order $order, Payment $payment): PaymentInitResult
            {
                return new PaymentInitResult('fake2_' . bin2hex(random_bytes(4)), 'https://example.test/fake2', '{}');
            }

            public function handleCallback(Request $request): PaymentCallbackResult
            {
                throw new RuntimeException('Not used by this test.');
            }

            public function getCallbackAcknowledgement(PaymentCallbackResult $result): string
            {
                return 'ok';
            }
        };
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
        verify($payment->currency)->equals('RUB');
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

    public function testInitiatingAgainWithTheSameProviderReusesThePendingPayment()
    {
        $order = $this->makeOrder(100);

        $first = $this->_handler->handle(new InitiatePaymentCommand(100, $order->id, 'fake'));
        $second = $this->_handler->handle(new InitiatePaymentCommand(100, $order->id, 'fake'));

        verify($second->id)->equals($first->id);
        verify(Payment::find()->where(['order_id' => $order->id])->count())->equals(1);
    }

    public function testSwitchingProviderSupersedesThePreviousPendingPayment()
    {
        $order = $this->makeOrder(100);

        $first = $this->_handler->handle(new InitiatePaymentCommand(100, $order->id, 'fake'));
        $second = $this->_handler->handle(new InitiatePaymentCommand(100, $order->id, 'robokassa'));

        $first->refresh();
        verify($first->status)->equals(Payment::STATUS_CANCELED);
        verify($second->status)->equals(Payment::STATUS_PENDING);
        verify($second->provider)->equals('robokassa');
        verify(Payment::find()->where(['order_id' => $order->id, 'status' => Payment::STATUS_PENDING])->count())->equals(1);
    }

    /**
     * Regression test: Payment::rules() defaults currency to 'RUB', but
     * that's a *validation-time* default — createPayment() is called
     * before the row is ever saved/validated, so a gateway reading
     * $payment->currency (as YooKassaGateway does) must see it already
     * set, not null.
     */
    public function testCurrencyIsAlreadySetWhenTheGatewayIsCalled()
    {
        $order = $this->makeOrder(100);
        $seen = new \stdClass();
        $seen->currency = 'not set';

        $handler = new InitiatePaymentHandler(new PaymentGatewayRegistry([
            new class ($seen) implements PaymentGatewayInterface {
                public function __construct(private \stdClass $seen)
                {
                }

                public function getCode(): PaymentProvider
                {
                    return PaymentProvider::Fake;
                }

                public function getLabel(): string
                {
                    return 'Currency probe';
                }

                public function createPayment(Order $order, Payment $payment): PaymentInitResult
                {
                    $this->seen->currency = $payment->currency;

                    return new PaymentInitResult('probe_1', 'https://example.test', '{}');
                }

                public function handleCallback(Request $request): PaymentCallbackResult
                {
                    throw new RuntimeException('Not used by this test.');
                }

                public function getCallbackAcknowledgement(PaymentCallbackResult $result): string
                {
                    return 'ok';
                }
            },
        ]));

        $handler->handle(new InitiatePaymentCommand(100, $order->id, 'fake'));

        verify($seen->currency)->equals('RUB');
    }
}
