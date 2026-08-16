---
title: Use HttpCache for Read-Heavy, Rarely-Changing Actions
impact: MEDIUM
impactDescription: Lets browsers/proxies skip re-requesting unchanged content entirely
tags: cache, http-caching, httpcache-filter, etag
---

## Use HttpCache for Read-Heavy, Rarely-Changing Actions

**Impact: MEDIUM**

`yii\filters\HttpCache` lets the client (and any intermediate proxy) skip re-fetching content that hasn't changed, via `Last-Modified` and/or `ETag` headers. Attach it as a controller filter for `GET`/`HEAD` actions whose output only changes when specific underlying data changes.

## Bad Example

```php
<?php
class ArticleController extends \yii\web\Controller
{
    // Full page render on every request, even when the article hasn't
    // changed since the visitor's last visit
    public function actionView($id)
    {
        return $this->render('view', ['model' => Article::findOne($id)]);
    }
}
```

## Good Example

```php
<?php
class ArticleController extends \yii\web\Controller
{
    public function behaviors()
    {
        return [
            'httpCache' => [
                'class' => \yii\filters\HttpCache::class,
                'only' => ['view'],
                'lastModified' => function ($action, $params) {
                    return Article::findOne(Yii::$app->request->get('id'))->updated_at;
                },
            ],
        ];
    }

    public function actionView($id)
    {
        return $this->render('view', ['model' => Article::findOne($id)]);
    }
}
```

## Why

- **304 Not Modified skips the render entirely**: when the client's cached copy is still fresh, the browser sends `If-Modified-Since`/`If-None-Match` and the server can respond without re-rendering.
- **`etagSeed` for finer-grained invalidation**: use it when "changed" isn't well captured by a single timestamp column.
- **Set `sessionCacheLimiter` deliberately**: PHP's default session cache headers can conflict with `HttpCache`'s own headers if left unconfigured.

Reference: [HTTP Caching Guide](https://www.yiiframework.com/doc/guide/2.0/en/caching-http)
