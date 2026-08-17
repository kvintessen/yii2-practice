<?php

declare(strict_types=1);

namespace app\tests\Functional;

use app\models\Order;
use app\tests\Support\Fixtures\UserFixture;
use app\tests\Support\FunctionalTester;
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
}
