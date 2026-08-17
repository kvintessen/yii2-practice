<?php

declare(strict_types=1);

namespace app\tests\Functional;

use app\models\Order;
use app\models\Payment;
use app\tests\Support\Fixtures\UserFixture;
use app\tests\Support\FunctionalTester;
use Yii;
use yii\helpers\Url;

final class PaymentCest
{
    public function _fixtures(): array
    {
        return ['users' => UserFixture::class];
    }

    private function makeOrderForUser(int $userId, string $total): Order
    {
        $order = new Order([
            'user_id' => $userId,
            'status' => Order::STATUS_NEW,
            'total' => $total,
        ]);
        $order->save();

        return $order;
    }

    public function buyerCanPayWithTheFakeGatewayAndOrderBecomesPaid(FunctionalTester $I)
    {
        $order = $this->makeOrderForUser(100, '30.00');

        $I->amLoggedInAs(100);
        $I->amOnRoute('payment/pay', ['orderId' => $order->id]);
        $I->see('Test gateway (no real charge)');

        $I->submitForm('#payment-pay-form', ['provider' => 'fake']);
        $I->see('Payment status', 'h1');
        $I->see('pending');

        // Stands in for the provider's server calling our webhook.
        $I->submitForm('#simulate-success-form', []);

        $order->refresh();
        $I->assertEquals(Order::STATUS_PAID, $order->status);
    }

    public function canceledPaymentLeavesOrderUnpaidAndBuyerCanRetry(FunctionalTester $I)
    {
        $order = $this->makeOrderForUser(100, '30.00');

        $I->amLoggedInAs(100);
        $I->amOnRoute('payment/pay', ['orderId' => $order->id]);
        $I->submitForm('#payment-pay-form', ['provider' => 'fake']);
        $I->submitForm('#simulate-failure-form', []);

        $order->refresh();
        $I->assertEquals(Order::STATUS_NEW, $order->status);

        // Still unpaid, so "Pay now" on the order page must still work.
        $I->amOnRoute('order/view', ['id' => $order->id]);
        $I->see('Pay now');
    }

    public function buyerCannotPayForAnotherUsersOrder(FunctionalTester $I)
    {
        $order = $this->makeOrderForUser(101, '30.00');

        $I->amLoggedInAs(100);
        $I->amOnRoute('payment/pay', ['orderId' => $order->id]);
        $I->seeResponseCodeIs(404);
    }

    public function callbackForAnUnknownPaymentIsIgnoredWithoutError(FunctionalTester $I)
    {
        $I->sendAjaxPostRequest(
            Url::to(['/payment/callback', 'provider' => 'fake']),
            ['external_id' => 'does-not-exist', 'outcome' => 'succeeded'],
        );
        $I->seeResponseCodeIs(200);
    }

    public function robokassaAppearsAsAPaymentOption(FunctionalTester $I)
    {
        $order = $this->makeOrderForUser(100, '30.00');

        $I->amLoggedInAs(100);
        $I->amOnRoute('payment/pay', ['orderId' => $order->id]);
        $I->see('Bank card (Robokassa)');
    }

    public function robokassaCallbackWithAValidSignatureMarksTheOrderPaid(FunctionalTester $I)
    {
        $order = $this->makeOrderForUser(100, '30.00');

        $I->amLoggedInAs(100);
        $I->amOnRoute('payment/pay', ['orderId' => $order->id]);
        $I->submitForm('#payment-pay-form', ['provider' => 'robokassa']);

        $payment = Payment::find()->where(['order_id' => $order->id, 'provider' => 'robokassa'])->one();
        $I->assertNotNull($payment);

        // Same formula RobokassaGateway::handleCallback() checks against —
        // signed here with whatever Password #2 the test app was actually
        // configured with, so this doesn't depend on real env values.
        $password2 = Yii::$app->params['robokassaPassword2'];
        $signature = md5(sprintf('%s:%s:%s', $payment->amount, $payment->external_id, $password2));

        $I->sendAjaxPostRequest(Url::to(['/payment/callback', 'provider' => 'robokassa']), [
            'OutSum' => $payment->amount,
            'InvId' => $payment->external_id,
            'SignatureValue' => $signature,
        ]);

        $I->seeResponseCodeIs(200);
        // Robokassa keeps retrying until it sees exactly this — not a
        // generic "ok".
        $I->assertEquals('OK' . $payment->external_id, $I->grabPageSource());

        $order->refresh();
        $I->assertEquals(Order::STATUS_PAID, $order->status);
    }

    public function robokassaCallbackWithABadSignatureIsRejected(FunctionalTester $I)
    {
        $order = $this->makeOrderForUser(100, '30.00');

        $I->amLoggedInAs(100);
        $I->amOnRoute('payment/pay', ['orderId' => $order->id]);
        $I->submitForm('#payment-pay-form', ['provider' => 'robokassa']);

        $payment = Payment::find()->where(['order_id' => $order->id, 'provider' => 'robokassa'])->one();

        $I->sendAjaxPostRequest(Url::to(['/payment/callback', 'provider' => 'robokassa']), [
            'OutSum' => $payment->amount,
            'InvId' => $payment->external_id,
            'SignatureValue' => 'not-a-real-signature',
        ]);

        $I->seeResponseCodeIs(400);

        $order->refresh();
        $I->assertEquals(Order::STATUS_NEW, $order->status);
    }
}
