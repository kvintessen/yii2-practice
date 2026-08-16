---
title: Build REST Endpoints on ActiveController
impact: MEDIUM
impactDescription: Standard CRUD REST actions and content negotiation without boilerplate
tags: rest, activecontroller, urlmanager, api
---

## Build REST Endpoints on ActiveController

**Impact: MEDIUM**

`yii\rest\ActiveController` implements the standard set of RESTful CRUD actions (index/view/create/update/delete) for a given `modelClass`, including content negotiation (JSON/XML) and pagination. Pair it with `urlManager` configured for pretty, strictly-parsed URLs and a `yii\rest\UrlRule`, instead of hand-writing a controller action per HTTP verb.

## Bad Example

```php
<?php
class UserController extends \yii\web\Controller
{
    // Reimplements index/view/create/update/delete by hand, plus manual
    // content-type negotiation and pagination — all of which ActiveController provides
    public function actionIndex() { /* ... */ }
    public function actionView($id) { /* ... */ }
    public function actionCreate() { /* ... */ }
    public function actionUpdate($id) { /* ... */ }
    public function actionDelete($id) { /* ... */ }
}
```

## Good Example

```php
<?php
namespace app\controllers;

class UserController extends \yii\rest\ActiveController
{
    public $modelClass = 'app\models\User';
}
```

```php
<?php
// config/web.php
'components' => [
    'urlManager' => [
        'enablePrettyUrl' => true,
        'enableStrictParsing' => true,
        'showScriptName' => false,
        'rules' => [
            ['class' => 'yii\rest\UrlRule', 'controller' => 'user'],
        ],
    ],
],
```

## Why

- **8 standard endpoints for free**: `GET /users`, `GET /users/1`, `POST /users`, `PATCH/PUT /users/1`, `DELETE /users/1` (plus `HEAD`/`OPTIONS`) come from one property assignment.
- **Content negotiation built in**: `Accept: application/json` / `application/xml` are handled automatically.
- **Override only what's non-standard**: custom actions or behaviors (auth, rate limiting, field filtering) layer on top via `behaviors()`/`actions()` overrides rather than reimplementing the whole controller.

Reference: [REST Quick Start Guide](https://www.yiiframework.com/doc/guide/2.0/en/rest-quick-start)
