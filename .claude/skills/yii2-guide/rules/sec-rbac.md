---
title: Authorize with RBAC, Not Scattered Role Checks
impact: CRITICAL
impactDescription: Centralizes and makes auditable who can do what
tags: sec, rbac, authorization, authmanager
---

## Authorize with RBAC, Not Scattered Role Checks

**Impact: CRITICAL**

Configure the `authManager` component (`PhpManager` or `DbManager`) and define roles/permissions/rules through it, then check access with `Yii::$app->user->can('permissionName')`. Avoid hard-coded `if ($user->role === 'admin')` checks sprinkled through controllers — they're impossible to audit centrally and easy to forget in a new action.

## Bad Example

```php
<?php
public function actionDelete($id)
{
    // Hard-coded role check, duplicated across every action that needs it,
    // easy to miss adding to a new controller
    if (Yii::$app->user->identity->role !== 'admin') {
        throw new ForbiddenHttpException();
    }
    Post::findOne($id)->delete();
}
```

## Good Example

```php
<?php
// config/web.php
'components' => [
    'authManager' => [
        'class' => \yii\rbac\DbManager::class,
    ],
],
```

```php
<?php
// one-time setup (console command or migration)
$auth = Yii::$app->authManager;
$deletePost = $auth->createPermission('deletePost');
$auth->add($deletePost);

$admin = $auth->createRole('admin');
$auth->add($admin);
$auth->addChild($admin, $deletePost);
$auth->assign($admin, $userId);
```

```php
<?php
public function actionDelete($id)
{
    if (!Yii::$app->user->can('deletePost')) {
        throw new ForbiddenHttpException();
    }
    Post::findOne($id)->delete();
}
```

## Why

- **Single place to reason about permissions**: the whole permission hierarchy lives in `authManager` data, not scattered across controller conditionals.
- **Business rules supported**: RBAC rules (e.g. "only the post's author can edit it") attach as executable conditions evaluated during `can()`, via `Yii::$app->user->can('updatePost', ['post' => $post])`.
- **Roles compose**: a role can include other roles/permissions, so `admin` can inherit everything `editor` has instead of duplicating grants.

Reference: [Authorization Guide (RBAC)](https://www.yiiframework.com/doc/guide/2.0/en/security-authorization)
