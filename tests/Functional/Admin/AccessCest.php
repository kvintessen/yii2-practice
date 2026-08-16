<?php

declare(strict_types=1);

namespace app\tests\Functional\Admin;

use app\tests\Support\Fixtures\AdminRoleAssignmentFixture;
use app\tests\Support\Fixtures\UserFixture;
use app\tests\Support\FunctionalTester;

final class AccessCest
{
    public function _fixtures(): array
    {
        return [
            'users' => UserFixture::class,
            'adminRole' => AdminRoleAssignmentFixture::class,
        ];
    }

    public function guestIsRedirectedToLogin(FunctionalTester $I)
    {
        // amOnRoute() follows the redirect automatically, so assert on where it lands.
        $I->amOnRoute('admin/category/index');
        $I->seeInCurrentUrl('admin%2Fsite%2Flogin');
        $I->see('Admin sign in');
    }

    public function nonAdminIsForbidden(FunctionalTester $I)
    {
        $I->amLoggedInAsAdmin(101); // demo, no 'admin' role
        $I->amOnRoute('admin/category/index');
        $I->seeResponseCodeIs(403);
    }

    public function adminCanReachTheDashboard(FunctionalTester $I)
    {
        $I->amLoggedInAsAdmin(100); // admin, has the 'admin' role
        $I->amOnRoute('admin/category/index');
        $I->seeResponseCodeIsSuccessful();
        $I->see('Categories', 'h1');
    }

    public function storefrontLoginDoesNotGrantAdminAccess(FunctionalTester $I)
    {
        $I->amLoggedInAs(100); // storefront session only, not adminUser
        $I->amOnRoute('admin/category/index');
        $I->seeInCurrentUrl('admin%2Fsite%2Flogin');
    }
}
