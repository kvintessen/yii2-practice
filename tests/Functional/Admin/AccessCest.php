<?php

declare(strict_types=1);

namespace app\tests\Functional\Admin;

use app\tests\Support\FunctionalTester;

final class AccessCest
{
    public function guestIsRedirectedToLogin(FunctionalTester $I)
    {
        // amOnRoute() follows the redirect automatically, so assert on where it lands.
        $I->amOnRoute('admin/category/index');
        $I->seeInCurrentUrl('site%2Flogin');
        $I->see('Login to your account');
    }

    public function nonAdminIsForbidden(FunctionalTester $I)
    {
        $I->amLoggedInAs(101); // demo, no 'admin' role
        $I->amOnRoute('admin/category/index');
        $I->seeResponseCodeIs(403);
    }

    public function adminCanReachTheDashboard(FunctionalTester $I)
    {
        $I->amLoggedInAs(100); // admin, has the 'admin' role
        $I->amOnRoute('admin/category/index');
        $I->seeResponseCodeIsSuccessful();
        $I->see('Categories', 'h1');
    }
}
