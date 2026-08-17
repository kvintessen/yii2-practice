<?php

declare(strict_types=1);

namespace app\tests\Unit\Models;

use app\models\Order;
use app\models\Payment;
use app\tests\Support\Fixtures\UserFixture;
use yii\db\IntegrityException;

final class PaymentTest extends \Codeception\Test\Unit
{
    public function _fixtures(): array
    {
        return ['users' => UserFixture::class];
    }

    private function makeOrder(): Order
    {
        $order = new Order(['user_id' => 100, 'status' => Order::STATUS_NEW, 'total' => '10.00']);
        $order->save();

        return $order;
    }

    public function testDatabaseRejectsASecondPendingPaymentForTheSameOrder()
    {
        $order = $this->makeOrder();

        $first = new Payment([
            'order_id' => $order->id,
            'provider' => 'fake',
            'status' => Payment::STATUS_PENDING,
            'amount' => '10.00',
            'external_id' => 'first',
        ]);
        $first->save();

        $second = new Payment([
            'order_id' => $order->id,
            'provider' => 'fake2',
            'status' => Payment::STATUS_PENDING,
            'amount' => '10.00',
            'external_id' => 'second',
        ]);

        // This is the safety net InitiatePaymentHandler relies on for the
        // true concurrent-request case — bypass app-level validation
        // (save(false)) to prove the database itself refuses two pending
        // rows for one order, independent of any application logic.
        $this->expectException(IntegrityException::class);
        $second->save(false);
    }

    public function testDatabaseAllowsASecondNonPendingPaymentForTheSameOrder()
    {
        $order = $this->makeOrder();

        $first = new Payment([
            'order_id' => $order->id,
            'provider' => 'fake',
            'status' => Payment::STATUS_CANCELED,
            'amount' => '10.00',
            'external_id' => 'first',
        ]);
        $first->save();

        $second = new Payment([
            'order_id' => $order->id,
            'provider' => 'fake2',
            'status' => Payment::STATUS_PENDING,
            'amount' => '10.00',
            'external_id' => 'second',
        ]);

        verify($second->save())->true();
    }

    public function testExternalIdMustBeUniquePerProvider()
    {
        $orderA = $this->makeOrder();
        $orderB = $this->makeOrder();

        $first = new Payment([
            'order_id' => $orderA->id,
            'provider' => 'fake',
            'status' => Payment::STATUS_SUCCEEDED,
            'amount' => '10.00',
            'external_id' => 'dup',
        ]);
        $first->save();

        $second = new Payment([
            'order_id' => $orderB->id,
            'provider' => 'fake',
            'status' => Payment::STATUS_SUCCEEDED,
            'amount' => '10.00',
            'external_id' => 'dup',
        ]);

        verify($second->validate())->false();
        verify($second->hasErrors('external_id'))->true();
    }
}
