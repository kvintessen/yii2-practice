---
title: Cache Keys Must Reflect Every Determining Factor
impact: MEDIUM
impactDescription: Prevents cross-user data leaks and stale-data bugs
tags: cache, data-caching, dependency, getorset
---

## Cache Keys Must Reflect Every Determining Factor

**Impact: MEDIUM**

`Yii::$app->cache->getOrSet($key, $callback, $duration, $dependency)` is the standard way to cache a computed value. The key must encode every input that affects the result (user ID, locale, filter params, ...) — otherwise different requests collide on the same cache entry. Pair time-based expiry with a `Dependency` when the data can change before the duration elapses.

## Bad Example

```php
<?php
// Same key for every user — user A's dashboard gets cached and served to user B
$data = Yii::$app->cache->get('dashboard');
if ($data === false) {
    $data = $this->buildDashboard(Yii::$app->user->id);
    Yii::$app->cache->set('dashboard', $data, 3600);
}
```

## Good Example

```php
<?php
use yii\caching\TagDependency;

$userId = Yii::$app->user->id;
$data = Yii::$app->cache->getOrSet(
    ['dashboard', 'user' => $userId],
    fn () => $this->buildDashboard($userId),
    3600,
    new TagDependency(['tags' => "user-$userId-dashboard"])
);

// elsewhere, when the user's underlying data changes:
TagDependency::invalidate(Yii::$app->cache, "user-$userId-dashboard");
```

## Why

- **Key uniqueness prevents leaks**: encoding `user`/locale/params in the key array means requests with different inputs never collide.
- **`getOrSet()` collapses the get/compute/set boilerplate** into one call, including closures with captured variables.
- **Dependencies invalidate on real change**, not just a fixed TTL — `TagDependency::invalidate()` clears every entry tagged for that user without knowing individual keys.

Reference: [Caching Data Guide](https://www.yiiframework.com/doc/guide/2.0/en/caching-data)
