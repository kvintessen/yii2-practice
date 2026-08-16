<?php

declare(strict_types=1);

namespace app\tests\Acceptance;

use app\models\User;
use app\tests\Support\AcceptanceTester;
use yii\helpers\Url;

final class SignupCest
{
    public function _after(AcceptanceTester $I)
    {
        // acceptance tests hit the app over real HTTP, so the Yii2 module's
        // per-test transaction rollback does not apply here; clean up manually.
        User::deleteAll(['username' => 'acceptance_user']);
    }

    public function ensureThatSignupWorks(AcceptanceTester $I)
    {
        $I->amOnPage(Url::toRoute('/site/signup'));
        $I->see('Create an account', 'h1');

        $I->amGoingTo('sign up with a new account');
        $I->fillField('input[name="SignupForm[username]"]', 'acceptance_user');
        $I->fillField('input[name="SignupForm[email]"]', 'acceptance_user@example.com');
        $I->fillField('input[name="SignupForm[password]"]', 'password1');
        $I->fillField('input[name="SignupForm[passwordRepeat]"]', 'password1');
        $I->click('signup-button');

        $I->expectTo('be logged in as the new user');
        $I->see('Logout');
    }
}
