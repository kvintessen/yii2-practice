<?php

declare(strict_types=1);

namespace app\tests\Functional\Admin;

use app\models\Order;
use app\tests\Support\Fixtures\AdminRoleAssignmentFixture;
use app\tests\Support\Fixtures\UserFixture;
use app\tests\Support\FunctionalTester;
use yii\helpers\Url;

final class OrderCest
{
    public function _fixtures(): array
    {
        return [
            'users' => UserFixture::class,
            'adminRole' => AdminRoleAssignmentFixture::class,
        ];
    }

    public function _before(FunctionalTester $I)
    {
        $I->amLoggedInAs(100); // admin
    }

    public function adminCanListOrders(FunctionalTester $I)
    {
        $order = new Order(['user_id' => 101, 'status' => Order::STATUS_NEW, 'total' => '44.44']);
        $order->save();

        $I->amOnRoute('admin/order/index');
        $I->see('44.44');
        $I->see('demo');
    }

    public function adminCanChangeOrderStatus(FunctionalTester $I)
    {
        $order = new Order(['user_id' => 101, 'status' => Order::STATUS_NEW, 'total' => '10.00']);
        $order->save();

        $I->sendAjaxRequest(
            'POST',
            Url::to(['/admin/order/update-status', 'id' => $order->id]),
            ['status' => Order::STATUS_PAID],
        );
        $I->seeResponseCodeIsRedirection();

        $order->refresh();
        $I->assertEquals(Order::STATUS_PAID, $order->status);
    }
}
