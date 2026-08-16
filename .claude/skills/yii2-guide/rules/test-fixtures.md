---
title: Use ActiveFixture for Deterministic Test Data
impact: MEDIUM
impactDescription: Removes order-dependence and flakiness from tests relying on DB state
tags: test, fixtures, activefixture, codeception
---

## Use ActiveFixture for Deterministic Test Data

**Impact: MEDIUM**

Define an `ActiveFixture` per model, backed by a data file under `tests/Support/data/`, and declare it in the test class's `_fixtures()` method. Fixtures reset to a known state before each test, unlike relying on whatever rows happen to already exist in the test database.

## Bad Example

```php
<?php
class UserRepositoryTest extends \Codeception\Test\Unit
{
    public function testFindByUsername()
    {
        // Assumes a row inserted manually or left over from a previous test run —
        // passes locally, fails in CI or after another test deletes that row
        $user = User::findOne(['username' => 'admin']);
        $this->assertNotNull($user);
    }
}
```

## Good Example

```php
<?php
// tests/Support/Fixtures/UserFixture.php
class UserFixture extends \yii\test\ActiveFixture
{
    public $modelClass = 'app\models\User';
}
```

```php
<?php
// tests/Support/data/user.php
return [
    'admin' => [
        'username' => 'admin',
        'email' => 'admin@example.com',
        'password_hash' => Yii::$app->security->generatePasswordHash('password'),
    ],
];
```

```php
<?php
class UserRepositoryTest extends \Codeception\Test\Unit
{
    public function _fixtures()
    {
        return ['users' => UserFixture::class];
    }

    public function testFindByUsername()
    {
        $user = User::findOne(['username' => 'admin']);
        $this->assertNotNull($user);
        $this->assertSame('admin@example.com', $user->email);
    }
}
```

## Why

- **Deterministic and order-independent**: the fixture reloads the declared rows before each test, so tests don't depend on execution order or leftover state.
- **Named row access**: `$I->grabFixture('users', 'admin')` (or the fixture object directly in Unit tests) references a specific row by alias instead of a magic ID.
- **Matches this project's existing setup**: `codeception.yml` already configures the `Yii2` module against `config/test.php` / `config/test_db.php` for exactly this pattern.

Reference: [Fixtures Guide](https://www.yiiframework.com/doc/guide/2.0/en/test-fixtures)
