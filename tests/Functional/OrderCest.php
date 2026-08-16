<?php

declare(strict_types=1);

namespace app\tests\Functional;

use app\models\Order;
use app\tests\Support\Fixtures\UserFixture;
use app\tests\Support\FunctionalTester;

final class OrderCest
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

    public function userSeesOnlyOwnOrdersInIndex(FunctionalTester $I)
    {
        $this->makeOrderForUser(100, '11.11');
        $this->makeOrderForUser(101, '22.22');

        $I->amLoggedInAs(100);
        $I->amOnRoute('order/index');
        $I->see('11.11');
        $I->dontSee('22.22');
    }

    public function userCannotViewAnotherUsersOrder(FunctionalTester $I)
    {
        $order = $this->makeOrderForUser(101, '33.33');

        $I->amLoggedInAs(100);
        $I->amOnRoute('order/view', ['id' => $order->id]);
        $I->seeResponseCodeIs(403);
    }
}
