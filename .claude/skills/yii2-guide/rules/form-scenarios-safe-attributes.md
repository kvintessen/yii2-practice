---
title: Scenarios and Safe Attributes Prevent Mass Assignment
impact: CRITICAL
impactDescription: Blocks attackers from setting fields like is_admin via extra POST params
tags: form, scenarios, safe-attributes, mass-assignment, security
---

## Scenarios and Safe Attributes Prevent Mass Assignment

**Impact: CRITICAL**

`load()` only mass-assigns attributes that are both listed in an active rule and part of the current scenario ("safe" attributes) — attributes without a matching rule are silently ignored. Use `scenarios()` to restrict which attributes are safe in which context (e.g. a public signup form must never make `is_admin` assignable).

## Bad Example

```php
<?php
class User extends \yii\db\ActiveRecord
{
    public function rules()
    {
        return [
            [['username', 'email', 'password', 'is_admin'], 'safe'], // is_admin mass-assignable everywhere
        ];
    }
}

// Public signup controller
$user = new User();
$user->load(Yii::$app->request->post()); // attacker adds User[is_admin]=1 to the POST body
$user->save();
```

## Good Example

```php
<?php
class User extends \yii\db\ActiveRecord
{
    const SCENARIO_SIGNUP = 'signup';

    public function rules()
    {
        return [
            [['username', 'email', 'password'], 'required'],
            [['username', 'email', 'password'], 'string', 'on' => self::SCENARIO_SIGNUP],
            ['is_admin', 'boolean'], // exists as a rule, but not exposed to the signup scenario
        ];
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_SIGNUP] = ['username', 'email', 'password']; // is_admin intentionally excluded
        return $scenarios;
    }
}

$user = new User(['scenario' => User::SCENARIO_SIGNUP]);
$user->load(Yii::$app->request->post()); // is_admin is ignored even if present in POST
$user->save();
```

## Why

- **Closes the classic mass-assignment hole**: fields like `is_admin`, `role`, `balance` stay unassignable from user-controlled input unless explicitly whitelisted per scenario.
- **Context-appropriate validation**: password may be required on `signup` but irrelevant on `update-profile`.
- **Fails safe**: an attribute missing from `scenarios()` for the active scenario is simply not set by `load()` — no exception, no silent full-attribute exposure.

Reference: [Validating Input Guide — Scenarios](https://www.yiiframework.com/doc/guide/2.0/en/input-validation)
