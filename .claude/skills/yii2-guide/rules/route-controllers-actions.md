---
title: One actionXxx() Method per Operation, Not a Param-Branched Mega-Action
impact: MEDIUM
impactDescription: Keeps routes self-documenting and lets filters/behaviors target specific actions
tags: route, controllers, actions, naming
---

## One actionXxx() Method per Operation, Not a Param-Branched Mega-Action

**Impact: MEDIUM**

Yii2 resolves a route (`site/index` → `SiteController::actionIndex()`) by convention: the controller ID maps to `{Name}Controller`, the action ID maps to `action{Name}()`, in camelCase from the URL's kebab-case. Give each operation its own `actionXxx()` with typed parameters, instead of one action branching on a hidden `mode`/`type` parameter.

## Bad Example

```php
<?php
class PostController extends \yii\web\Controller
{
    // One route, hidden behavior selected by an untyped param —
    // access control, HTTP verb filters, and rate limiting can't target
    // "publish" vs "unpublish" separately since it's all one action
    public function actionManage($id, $mode)
    {
        $post = Post::findOne($id);
        if ($mode === 'publish') {
            $post->status = Post::STATUS_PUBLISHED;
        } elseif ($mode === 'unpublish') {
            $post->status = Post::STATUS_DRAFT;
        }
        $post->save();
    }
}
```

## Good Example

```php
<?php
class PostController extends \yii\web\Controller
{
    public function actionPublish(int $id)
    {
        $post = Post::findOne($id);
        $post->status = Post::STATUS_PUBLISHED;
        $post->save();
    }

    public function actionUnpublish(int $id)
    {
        $post = Post::findOne($id);
        $post->status = Post::STATUS_DRAFT;
        $post->save();
    }

    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => \yii\filters\VerbFilter::class,
                'actions' => [
                    'publish' => ['post'],
                    'unpublish' => ['post'],
                ],
            ],
        ];
    }
}
```

## Why

- **Filters/behaviors target individual actions**: `VerbFilter`, access rules, and rate limiting can all be scoped per action ID (`publish` vs `unpublish`) once they're separate methods.
- **Self-documenting routes**: `post/publish` and `post/unpublish` are legible in logs, access-control config, and generated URLs; `post/manage?mode=publish` isn't.
- **Typed action parameters** (`int $id`) get automatic type coercion/validation from Yii's action parameter binding, catching malformed input before the action body runs.

Reference: [Routing Guide — Creating URL Rules](https://www.yiiframework.com/doc/guide/2.0/en/runtime-routing)
