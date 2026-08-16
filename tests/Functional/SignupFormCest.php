<?php

declare(strict_types=1);

namespace app\tests\Functional;

use app\tests\Support\FunctionalTester;

final class SignupFormCest
{
    public function _before(FunctionalTester $I)
    {
        $I->amOnRoute('site/signup');
    }

    public function openSignupPage(FunctionalTester $I)
    {
        $I->see('Create an account', 'h1');
    }

    public function signupWithEmptyCredentials(FunctionalTester $I)
    {
        $I->submitForm('#signup-form', []);
        $I->expectTo('see validation errors');
        $I->see('Username cannot be blank.');
        $I->see('Email cannot be blank.');
        $I->see('Password cannot be blank.');
    }

    public function signupWithMismatchedPasswords(FunctionalTester $I)
    {
        $I->submitForm('#signup-form', [
            'SignupForm[username]' => 'new_user',
            'SignupForm[email]' => 'new_user@example.com',
            'SignupForm[password]' => 'password1',
            'SignupForm[passwordRepeat]' => 'password2',
        ]);
        $I->expectTo('see validation errors');
        $I->see('Passwords do not match.');
    }

    public function signupWithTakenUsername(FunctionalTester $I)
    {
        $I->submitForm('#signup-form', [
            'SignupForm[username]' => 'admin',
            'SignupForm[email]' => 'another_admin@example.com',
            'SignupForm[password]' => 'password1',
            'SignupForm[passwordRepeat]' => 'password1',
        ]);
        $I->expectTo('see validation errors');
        $I->see('This username has already been taken.');
    }

    public function signupSuccessfully(FunctionalTester $I)
    {
        $I->submitForm('#signup-form', [
            'SignupForm[username]' => 'brand_new_user',
            'SignupForm[email]' => 'brand_new_user@example.com',
            'SignupForm[password]' => 'password1',
            'SignupForm[passwordRepeat]' => 'password1',
        ]);
        $I->see('Logout (brand_new_user)');
        $I->dontSeeElement('form#signup-form');
    }
}
