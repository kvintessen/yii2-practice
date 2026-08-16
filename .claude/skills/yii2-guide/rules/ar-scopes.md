---
title: Reusable Query Scopes via a Custom ActiveQuery Class
impact: MEDIUM
impactDescription: Removes duplicated where() conditions scattered across controllers/services
tags: ar, activequery, scopes, reuse
---

## Reusable Query Scopes via a Custom ActiveQuery Class

**Impact: MEDIUM**

Yii 1.1-style "scopes" are implemented in Yii2 by extending `ActiveQuery` with named, chainable methods, then overriding `find()` on the AR class to return that custom query object. Use `andOnCondition()`/`orOnCondition()` inside scope methods so they compose instead of overwriting each other.

## Bad Example

```php
<?php
// The "active comment" condition is copy-pasted everywhere it's needed
$active = Comment::find()->where(['active' => true])->all();
// ... elsewhere in the codebase, same condition retyped:
$activeForPost = Comment::find()->where(['active' => true, 'post_id' => $postId])->all();
```

## Good Example

```php
<?php
class CommentQuery extends \yii\db\ActiveQuery
{
    public function active($state = true)
    {
        return $this->andOnCondition(['active' => $state]);
    }
}

class Comment extends \yii\db\ActiveRecord
{
    public static function find()
    {
        return new CommentQuery(static::class);
    }
}

// usage — chainable, composes with other conditions and eager loading
$active = Comment::find()->active()->all();
$activeForPost = Comment::find()->active()->andWhere(['post_id' => $postId])->all();
```

## Why

- **Single definition of the condition**: `active()` is defined once and reused everywhere, including inside relation declarations.
- **Chainable**: composes naturally with `with()`, `andWhere()`, and other query methods.
- **`andOnCondition()` over `where()`**: preserves existing conditions (important when the scope is used inside a relation's `on` condition) instead of silently overwriting them.

Reference: [Active Record Guide — Customizing Query Classes](https://www.yiiframework.com/doc/guide/2.0/en/db-active-record)
