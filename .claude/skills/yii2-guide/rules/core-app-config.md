---
title: Application Configuration Structure
impact: HIGH
impactDescription: Keeps environment-specific and secret values out of code, avoids accidental prod misconfiguration
tags: core, config, application, bootstrap, params
---

## Application Configuration Structure

**Impact: HIGH**

A Yii2 application is assembled from a plain PHP array: `components` registers services, `params` holds globally accessible tunables, `bootstrap` lists components/modules to run on every request, and `basePath` anchors everything else. Keep environment-only concerns (debug/gii, credentials) out of the base config and gate them explicitly.

## Bad Example

```php
<?php
// config/web.php
return [
    'id' => 'app',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log', 'debug', 'gii'], // debug/gii bootstrapped unconditionally
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=app',
            'username' => 'root',
            'password' => 'hardcoded-secret', // secret baked into VCS-tracked config
        ],
    ],
];
```

## Good Example

```php
<?php
// config/web.php
$config = [
    'id' => 'app',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'components' => [
        'db' => require __DIR__ . '/db.php', // credentials pulled from env, see config/db.php
    ],
    'params' => require __DIR__ . '/params.php',
];

if (YII_ENV_DEV) {
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = ['class' => 'yii\debug\Module'];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = ['class' => 'yii\gii\Module'];
}

return $config;
```

## Why

- **No leaked debug tooling**: `debug`/`gii` modules only bootstrap under `YII_ENV_DEV`, so they can't accidentally ship to production.
- **Secrets stay out of tracked config**: DB credentials come from environment variables (this project's `config/db.php` + `.env`), not literals in `web.php`.
- **Single source of tunables**: `params.php` centralizes values referenced via `Yii::$app->params['key']` instead of scattering magic values through the codebase.

Reference: [Application Structure Guide](https://www.yiiframework.com/doc/guide/2.0/en/structure-applications)
