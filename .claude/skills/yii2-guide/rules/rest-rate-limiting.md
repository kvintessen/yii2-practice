---
title: Rate-Limit Public API Endpoints
impact: MEDIUM
impactDescription: Protects against abuse and accidental resource exhaustion
tags: rest, rate-limiting, api, security
---

## Rate-Limit Public API Endpoints

**Impact: MEDIUM**

Implement `RateLimitInterface` (`getRateLimit()`, `loadAllowance()`, `saveAllowance()`) on the identity class used by your API. `yii\filters\RateLimiter` attaches automatically to REST controllers once the current user's identity implements it, returning HTTP 429 once the limit is exceeded.

## Bad Example

```php
<?php
// No rate limiting anywhere — a single client (or a bug in a client's retry loop)
// can issue unlimited requests against expensive endpoints
class ApiUser extends \yii\db\ActiveRecord implements \yii\web\IdentityInterface
{
    // ... IdentityInterface methods only
}
```

## Good Example

```php
<?php
class ApiUser extends \yii\db\ActiveRecord implements
    \yii\web\IdentityInterface,
    \yii\filters\RateLimitInterface
{
    public function getRateLimit($request, $action)
    {
        return [100, 600]; // 100 requests per 600 seconds
    }

    public function loadAllowance($request, $action)
    {
        return [$this->allowance, $this->allowance_updated_at];
    }

    public function saveAllowance($request, $action, $allowance, $timestamp)
    {
        $this->updateAttributes([
            'allowance' => $allowance,
            'allowance_updated_at' => $timestamp,
        ]);
    }
}
```

## Why

- **Automatic once implemented**: `RateLimiter` attaches itself to REST controllers as soon as the authenticated identity implements `RateLimitInterface` — no per-controller wiring needed.
- **Standard headers for well-behaved clients**: `X-Rate-Limit-Limit`/`-Remaining`/`-Reset` let API consumers back off before hitting 429s.
- **Storage is flexible**: allowance can live in a DB column (as above), cache, or any store fast enough for per-request reads/writes.

Reference: [Rate Limiting Guide](https://www.yiiframework.com/doc/guide/2.0/en/rest-rate-limiting)
