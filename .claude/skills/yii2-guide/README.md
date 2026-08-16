# Yii2 Guide

Practical Yii2 2.0.x web development guidance, sourced from the official framework guide (yiiframework.com/doc/guide/2.0/en), for Active Record, validation, security, caching, REST, routing, and testing.

## Overview

**Important:** Check the project's Yii2/PHP versions (`composer.json`) before giving advice. This skill targets `yiisoft/yii2 ~2.0.54` on `php >=8.2`, matching this repo's `yiisoft/yii2-app-basic` template.

This skill provides guidance for:
- Active Record and Query Builder usage
- Model validation, scenarios, and safe attributes
- Security: CSRF, XSS, SQL injection, password hashing, RBAC, authentication
- Data/fragment/HTTP caching
- REST APIs with `ActiveController`
- URL routing and controller/action conventions
- Codeception testing (Unit/Functional/Acceptance) and fixtures

## Categories (32 rules)

### 1. Security (Critical) — 6 rules
CSRF protection, output encoding (XSS), SQL injection prevention, password hashing, RBAC authorization, `IdentityInterface` authentication.

### 2. Database & Active Record (Critical) — 6 rules
Query Builder parameter binding, transactional migrations, idiomatic AR CRUD, eager loading (N+1 prevention), transactions, reusable query scopes.

### 3. Configuration & Application Structure (High) — 6 rules
App config structure, path aliases, DI container, service locator, behaviors, events.

### 4. Forms & Validation (High) — 4 rules
`rules()`-based validation, `ActiveForm`, scenarios/safe attributes (mass-assignment protection), custom validators.

### 5. Caching (Medium) — 3 rules
Data caching with dependencies, fragment caching with variations, HTTP caching.

### 6. REST API (Medium) — 3 rules
`ActiveController`, field whitelisting for serialization, rate limiting.

### 7. Testing (Medium) — 2 rules
Codeception suite selection, `ActiveFixture` for deterministic test data.

### 8. Routing (Medium) — 2 rules
`urlManager` configuration, controller/action naming conventions.

## Usage

Ask Claude to:
- "Review my Yii2 code"
- "Check Active Record usage"
- "Audit Yii2 security"
- "Check Yii2 best practices"

## Key Guidelines

### Always Use
- `Html::encode()` / `HtmlPurifier` for any user-controlled output
- Query Builder / Active Record `where()` conditions (bound automatically) instead of raw SQL
- `Yii::$app->security->generatePasswordHash()` / `validatePassword()` for passwords
- `with()` to eager-load relations accessed in a loop
- `safeUp()`/`safeDown()` in migrations
- Scenarios to restrict which attributes are mass-assignable

### Avoid
- Disabling CSRF validation globally
- String-concatenated SQL, even for "just this one query"
- `md5()`/`sha1()` for password hashing
- Exposing all Active Record attributes through a REST `fields()` default
- Pretty URLs without `enableStrictParsing` and explicit rules
- Param-branched mega-actions instead of one `actionXxx()` per operation

## References

- [Yii2 Definitive Guide](https://www.yiiframework.com/doc/guide/2.0/en/)
- [Yii2 Class Reference](https://www.yiiframework.com/doc/api/2.0)
- [Security Best Practices](https://www.yiiframework.com/doc/guide/2.0/en/security-best-practices)
- [yiisoft/yii2 on GitHub](https://github.com/yiisoft/yii2)
