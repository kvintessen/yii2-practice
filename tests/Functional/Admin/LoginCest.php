<?php

declare(strict_types=1);

namespace app\tests\Functional\Admin;

use app\tests\Support\Fixtures\AdminRoleAssignmentFixture;
use app\tests\Support\Fixtures\UserFixture;
use app\tests\Support\FunctionalTester;

final class LoginCest
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
        $I->amOnRoute('admin/site/login');
    }

    public function openLoginPage(FunctionalTester $I)
    {
        $I->see('Admin sign in', 'h1');
    }

    public function nonAdminCredentialsAreRejected(FunctionalTester $I)
    {
        $I->submitForm('#admin-login-form', [
            'LoginForm[username]' => 'demo',
            'LoginForm[password]' => 'demo',
        ]);
        $I->see('This account does not have admin access.');
    }

    public function wrongPasswordIsRejected(FunctionalTester $I)
    {
        $I->submitForm('#admin-login-form', [
            'LoginForm[username]' => 'admin',
            'LoginForm[password]' => 'wrong',
        ]);
        $I->see('Incorrect username or password.');
    }

    public function adminCredentialsSignIn(FunctionalTester $I)
    {
        $I->submitForm('#admin-login-form', [
            'LoginForm[username]' => 'admin',
            'LoginForm[password]' => 'admin',
        ]);
        $I->see('Categories', 'h1');
        $I->dontSeeElement('form#admin-login-form');
    }
}
