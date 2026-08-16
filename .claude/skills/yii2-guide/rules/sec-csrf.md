---
title: Keep CSRF Protection Enabled
impact: CRITICAL
impactDescription: Default defense against cross-site request forgery on state-changing requests
tags: sec, csrf, security, forms
---

## Keep CSRF Protection Enabled

**Impact: CRITICAL**

Yii2 validates a CSRF token on unsafe HTTP methods by default (`enableCsrfValidation = true`). Disabling it globally to make an external tool's POST "just work" removes protection from every form in the app, not just the one endpoint that needed an exception.

## Bad Example

```php
<?php
// config/web.php
'components' => [
    'request' => [
        'enableCsrfValidation' => false, // disabled app-wide to unblock one Postman test
    ],
],
```

## Good Example

```php
<?php
// config/web.php — leave CSRF validation on by default
'components' => [
    'request' => [
        'cookieValidationKey' => getenv('COOKIE_VALIDATION_KEY'),
    ],
],

// Only the specific stateless API controller that genuinely needs it
// (e.g. authenticated via bearer token, not cookies) opts out, explicitly:
class ApiController extends \yii\rest\ActiveController
{
    public $enableCsrfValidation = false;
}
```

```php
<?php
// view — when submitting via raw AJAX instead of ActiveForm, include the token
?>
<?= Html::csrfMetaTags() ?>
```

## Why

- **Scoped exceptions, not a global switch**: disabling CSRF on one REST controller that uses token auth is very different from disabling it for every session-authenticated form in the app.
- **`ActiveForm` already includes the token**: forms built with `ActiveForm` submit the CSRF token automatically — the friction usually comes from hand-rolled AJAX requests, which should add `Html::csrfMetaTags()`/the `X-CSRF-Token` header instead.
- **`sameSite` cookie attribute** adds a second layer of browser-level CSRF mitigation alongside the token.

Reference: [Security Best Practices Guide](https://www.yiiframework.com/doc/guide/2.0/en/security-best-practices)
