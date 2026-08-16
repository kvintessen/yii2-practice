---
title: Vary Fragment Cache and Exempt Per-Request Content
impact: MEDIUM
impactDescription: Avoids serving one user's cached fragment (or a stale CSRF token) to another
tags: cache, fragment-caching, views, dynamic-content
---

## Vary Fragment Cache and Exempt Per-Request Content

**Impact: MEDIUM**

`$this->beginCache($id, $options)` / `$this->endCache()` in a view cache a rendered fragment. Use `variations` to split the cache per language/role/etc., and `renderDynamic()` for content that must always be fresh (CSRF tokens, "logged in as X") even inside an otherwise-cached fragment.

## Bad Example

```php
<?php
// A form fragment cached for an hour, embedding a CSRF token that goes stale
// the moment the session's token rotates — every visitor after the first
// submits with a token that no longer validates
if ($this->beginCache('sidebar-form', ['duration' => 3600])) {
    echo Html::beginForm();
    echo Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken);
    echo Html::endForm();
    $this->endCache();
}
```

## Good Example

```php
<?php
if ($this->beginCache('sidebar-form', [
    'duration' => 3600,
    'variations' => [Yii::$app->language],
])) {
    echo Html::beginForm();
    echo $this->renderDynamic('return Html::hiddenInput(
        Yii::$app->request->csrfParam,
        Yii::$app->request->csrfToken
    );'); // re-evaluated on every request, even when the surrounding fragment is served from cache
    echo Html::endForm();
    $this->endCache();
}
```

## Why

- **`variations` splits the cache key**: content that differs by language/role gets its own cache entry instead of one user's render leaking to another.
- **`renderDynamic()` for anything per-request**: CSRF tokens, "Hi, {name}" greetings, or ad slots stay fresh even while the surrounding markup is cached.
- **Nested fragments have independent lifetimes**: an inner cached block isn't invalidated when the outer one expires, and vice versa — account for that when choosing durations.

Reference: [Fragment Caching Guide](https://www.yiiframework.com/doc/guide/2.0/en/caching-fragment)
