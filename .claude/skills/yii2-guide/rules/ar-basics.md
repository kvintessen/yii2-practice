---
title: Idiomatic Active Record CRUD
impact: CRITICAL
impactDescription: Preserves validation, events, and behaviors that raw SQL bypasses
tags: ar, activerecord, crud
---

## Idiomatic Active Record CRUD

**Impact: CRITICAL**

For tables backed by a model, use `ActiveRecord::find()`/`save()`/`delete()` rather than dropping to raw `Yii::$app->db->createCommand()`. Raw commands skip validation, `beforeSave`/`afterSave` events, and attached behaviors (timestamps, etc.).

## Bad Example

```php
<?php
// Bypasses Customer's rules(), events, and any attached behaviors
Yii::$app->db->createCommand()->update(
    'customer',
    ['email' => $newEmail],
    ['id' => $id]
)->execute();
```

## Good Example

```php
<?php
$customer = Customer::findOne($id);
$customer->email = $newEmail;
$customer->save(); // runs validation, beforeSave/afterSave events, attached behaviors

// Create
$customer = new Customer();
$customer->name = 'James';
$customer->save();

// Delete
$customer->delete();
Customer::deleteAll(['status' => Customer::STATUS_INACTIVE]);
```

## Why

- **Validation runs automatically**: `save()` calls `validate()` first by default, rejecting bad data before it hits the database.
- **Events and behaviors fire**: timestamp updates, cache invalidation hooks, audit logging attached via behaviors/events all still run.
- **Consistent API**: `findOne()`/`find()->where()->all()` reads more clearly than assembling raw SQL for simple CRUD.

Reference: [Active Record Guide](https://www.yiiframework.com/doc/guide/2.0/en/db-active-record)
