---
name: yii2-guide
description: Yii2 2.0.x web development best practices sourced from the official framework guide — Active Record, validation, security (CSRF/XSS/SQLi/RBAC/passwords), caching, REST APIs, routing, and testing. Use when reviewing Yii2 code, writing models/controllers/migrations, checking Active Record usage, auditing Yii2 security, or asking "Yii2 best practices".
license: MIT
metadata:
  author: yii2-community
  version: "1.0.0"
  yii2Version: "2.0.x"
---

# Yii2 Guide

Practical Yii2 2.0.x web development guidance sourced from the official framework guide (yiiframework.com/doc/guide/2.0/en). Contains 32 rules across 8 categories, focused on what a Yii2 web app actually needs day to day — not framework internals.

## Step 1: Detect the Stack

**Check the project's Yii2 and PHP versions before giving advice.**

```bash
grep -A2 '"yiisoft/yii2"' composer.json   # e.g. "~2.0.54"
grep '"php"' composer.json                # e.g. ">=8.2"
php -v
```

This project (`yiisoft/yii2-app-basic`) pins `yiisoft/yii2: ~2.0.54` on `php: >=8.2`, uses Codeception (`codeception/codeception ^5.0`) with Unit/Functional/Acceptance suites, and `yii2-debug`/`yii2-gii` as dev-only modules. All examples in this skill target that baseline.

## When to Apply

Reference these guidelines when:
- Writing or reviewing models, controllers, migrations, or views
- Working with Active Record, Query Builder, or forms/validation
- Implementing authentication, authorization, or any security-sensitive code
- Adding caching, a REST endpoint, or a URL rule
- Writing or reviewing Codeception tests

## Rule Categories by Priority

| Priority | Category | Impact | Prefix | Rules |
|----------|----------|--------|--------|-------|
| 1 | Security | CRITICAL | `sec-` | 6 |
| 2 | Database & Active Record | CRITICAL | `db-` / `ar-` | 6 |
| 3 | Configuration & Application Structure | HIGH | `core-` | 6 |
| 4 | Forms & Validation | HIGH | `form-` | 4 |
| 5 | Caching | MEDIUM | `cache-` | 3 |
| 6 | REST API | MEDIUM | `rest-` | 3 |
| 7 | Testing | MEDIUM | `test-` | 2 |
| 8 | Routing | MEDIUM | `route-` | 2 |

## Quick Reference

### 1. Security (CRITICAL) — 6 rules

- `sec-csrf` - Keep CSRF protection enabled; scope exceptions narrowly
- `sec-output-encoding` - `Html::encode()`/`HtmlPurifier` to prevent XSS
- `sec-sql-injection` - Never concatenate user input into SQL
- `sec-password-hashing` - `security->generatePasswordHash()`, never MD5/SHA1
- `sec-rbac` - Authorize via `authManager`/`can()`, not scattered role checks
- `sec-authentication` - Implement `IdentityInterface` fully, don't roll your own session auth

### 2. Database & Active Record (CRITICAL) — 6 rules

- `db-query-builder` - Bound parameters via Query Builder, not raw SQL concatenation
- `db-migrations` - `safeUp()`/`safeDown()`, portable abstract column types
- `ar-basics` - Idiomatic `find()`/`save()`/`delete()` over raw `createCommand()`
- `ar-relations` - Eager-load with `with()` to avoid N+1 queries
- `ar-transactions` - Wrap multi-step writes in `db->transaction()`
- `ar-scopes` - Reusable query scopes via a custom `ActiveQuery` class

### 3. Configuration & Application Structure (HIGH) — 6 rules

- `core-app-config` - `bootstrap`/`components`/`params` structure, env-gated dev tooling
- `core-aliases` - Path aliases (`@app`, `@web`, `@runtime`) over hard-coded paths
- `core-di-container` - Constructor injection resolved via `Yii::createObject()`
- `core-service-locator` - `Yii::$app->get()` for shared components
- `core-behaviors` - `TimestampBehavior`-style mixins over duplicated lifecycle code
- `core-events` - `on()`/`trigger()`/`Event::on()` to decouple side effects

### 4. Forms & Validation (HIGH) — 4 rules

- `form-model-rules` - Validate through `rules()`, not ad hoc controller checks
- `form-activeform` - `ActiveForm` widget for coupled client+server validation
- `form-scenarios-safe-attributes` - Scenarios prevent mass-assignment vulnerabilities
- `form-custom-validators` - Inline/standalone validators, not post-save checks

### 5. Caching (MEDIUM) — 3 rules

- `cache-data` - Cache keys must encode every determining factor
- `cache-fragment` - `variations` + `renderDynamic()` for per-request content
- `cache-http` - `HttpCache` filter for read-heavy, rarely-changing actions

### 6. REST API (MEDIUM) — 3 rules

- `rest-active-controller` - `ActiveController` for standard CRUD REST endpoints
- `rest-serializer` - Whitelist `fields()`, don't expose sensitive columns by default
- `rest-rate-limiting` - `RateLimitInterface` on the API identity class

### 7. Testing (MEDIUM) — 2 rules

- `test-codeception-suites` - Match Unit/Functional/Acceptance to what's under test
- `test-fixtures` - `ActiveFixture` for deterministic, order-independent test data

### 8. Routing (MEDIUM) — 2 rules

- `route-url-rules` - `enableStrictParsing` + explicit rules, not wide-open pretty URLs
- `route-controllers-actions` - One `actionXxx()` per operation, not a param-branched mega-action

## Key Patterns (Quick Reference)

```php
<?php

// Query Builder / AR — bound parameters, never string concatenation
$rows = Customer::find()->where(['status' => $status])->all();

// Eager loading to avoid N+1
$customers = Customer::find()->with('orders')->all();

// Validation + mass-assignment protection
class SignupForm extends \yii\base\Model
{
    public function rules()
    {
        return [
            [['username', 'email', 'password'], 'required'],
            ['email', 'email'],
        ];
    }
}

// Password hashing
$user->password_hash = Yii::$app->security->generatePasswordHash($password);

// Authorization
if (Yii::$app->user->can('updatePost', ['post' => $post])) { /* ... */ }

// Output encoding
echo Html::encode($userSuppliedText);
```

## Output Format

When auditing code, output findings in this format:

```
file:line - [category] Description of issue
```

Example:
```
models/User.php:22 - [sec] Password hashed with md5() instead of security component
controllers/PostController.php:40 - [ar] N+1 query: relation accessed in a loop without with()
models/SignupForm.php:15 - [form] is_admin included in a scenario reachable from public signup
```

## How to Use

Read individual rule files for detailed explanations, sourced examples, and links to the official guide:

```
rules/sec-sql-injection.md
rules/ar-relations.md
rules/form-scenarios-safe-attributes.md
```
