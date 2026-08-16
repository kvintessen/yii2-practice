<?php

declare(strict_types=1);

namespace app\tests\Acceptance;

use app\tests\Support\AcceptanceTester;
use app\tests\Support\Fixtures\UserFixture;
use yii\helpers\Url;

final class LoginCest
{
    public function _fixtures(): array
    {
        return ['users' => UserFixture::class];
    }

    public function ensureThatLoginWorks(AcceptanceTester $I)
    {
        $I->amOnPage(Url::toRoute('/site/login'));
        $I->see('Login', 'h1');

        $I->amGoingTo('try to login with correct credentials');
        $I->fillField('input[name="LoginForm[username]"]', 'admin');
        $I->fillField('input[name="LoginForm[password]"]', 'admin');
        $I->click('login-button');

        $I->expectTo('see user info');
        $I->see('admin', '.dropdown-header');
        $I->see('Log out');
    }
}
