---
title: Match Codeception Suite to What's Under Test
impact: MEDIUM
impactDescription: Keeps the fast majority of tests fast; reserves slow browser tests for real user journeys
tags: test, codeception, unit, functional, acceptance
---

## Match Codeception Suite to What's Under Test

**Impact: MEDIUM**

This project ships three Codeception suites (`tests/Unit`, `tests/Functional`, `tests/Acceptance`, configured in `codeception.yml` with the `Yii2` module against `config/test.php`). Unit tests exercise a single class in isolation; Functional tests drive controller/request flow without a real browser; Acceptance tests drive a real browser end-to-end. Picking the wrong suite for a given check either makes the suite slow for no reason or fails to catch what it's meant to catch.

## Bad Example

```php
<?php
// tests/Acceptance/UserValidationCest.php
// A pure model-validation check doesn't need a browser — this is slow
// and couples a unit-level concern to acceptance infrastructure
class UserValidationCest
{
    public function checkEmailValidation(AcceptanceTester $I)
    {
        $I->amOnPage('/user/create');
        $I->fillField('User[email]', 'not-an-email');
        $I->click('Submit');
        $I->see('Email is not a valid email address.');
    }
}
```

## Good Example

```php
<?php
// tests/Unit/models/UserTest.php — isolated model logic, no HTTP/browser involved
class UserTest extends \Codeception\Test\Unit
{
    public function testEmailMustBeValid()
    {
        $user = new User(['email' => 'not-an-email']);
        $this->assertFalse($user->validate(['email']));
    }
}
```

```php
<?php
// tests/Functional/UserCreationCest.php — controller flow via request emulation, no real browser
class UserCreationCest
{
    public function cannotCreateUserWithInvalidEmail(FunctionalTester $I)
    {
        $I->amOnRoute('user/create');
        $I->submitForm('#user-form', ['User[email]' => 'not-an-email']);
        $I->see('Email is not a valid email address.');
    }
}
```

## Why

- **Unit for isolated logic**: model validation, service classes, helpers — fast, no framework bootstrapping beyond what's needed.
- **Functional for request/response flow**: controller actions, redirects, flash messages — via the `Yii2` Codeception module, without spinning up a real browser.
- **Acceptance sparingly, for real user journeys**: JS-dependent behavior or true end-to-end flows where a real browser is the point, since these are the slowest and most brittle to run.

Reference: [Testing Guide](https://www.yiiframework.com/doc/guide/2.0/en/test-environment-setup)
