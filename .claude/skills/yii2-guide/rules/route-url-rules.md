---
title: Configure urlManager Explicitly — Don't Leave Routing Wide Open
impact: MEDIUM
impactDescription: Prevents unintended controller/action pairs from being reachable
tags: route, urlmanager, pretty-url, security
---

## Configure urlManager Explicitly — Don't Leave Routing Wide Open

**Impact: MEDIUM**

Enable pretty URLs with `enableStrictParsing => true` and an explicit `rules` array, rather than pretty URLs with no strict parsing (which still resolves *any* `controller/action` path that exists, intentionally exposed or not) or the default `?r=` query-string routing left on in production.

## Bad Example

```php
<?php
// config/web.php
'components' => [
    'urlManager' => [
        'enablePrettyUrl' => true,
        // No enableStrictParsing, no rules: any existing controller/action
        // is reachable at /controller/action, including ones never meant
        // to be public routes
    ],
],
```

## Good Example

```php
<?php
// config/web.php
'components' => [
    'urlManager' => [
        'enablePrettyUrl' => true,
        'showScriptName' => false,
        'enableStrictParsing' => true,
        'rules' => [
            'post/<id:\d+>' => 'post/view',
            'posts' => 'post/index',
            '<controller:[\w-]+>/<action:[\w-]+>' => '<controller>/<action>',
        ],
    ],
],
```

## Why

- **`enableStrictParsing` closes the implicit-route gap**: only URLs matching a declared rule resolve; everything else 404s instead of falling through to any matching controller/action.
- **Typed parameters (`<id:\d+>`) validate at the routing layer**: malformed input never reaches the action, and rules stay short since one parameterized rule replaces many literal ones.
- **`showScriptName => false`** keeps `index.php` out of generated URLs once the web server is configured for URL rewriting.

Reference: [Routing Guide](https://www.yiiframework.com/doc/guide/2.0/en/runtime-routing)
