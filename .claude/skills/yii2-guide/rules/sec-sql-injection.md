---
title: Never Concatenate User Input into SQL
impact: CRITICAL
impactDescription: Eliminates SQL injection at the query-construction layer
tags: sec, sql-injection, security, query-builder
---

## Never Concatenate User Input into SQL

**Impact: CRITICAL**

Both Query Builder and Active Record route condition values through PDO parameter binding automatically when you use the hash/operator `where()` formats — but that guarantee only holds if you actually use them. Raw `createCommand($sql)` calls with interpolated variables reopen the same SQL injection hole the builder exists to close.

## Bad Example

```php
<?php
$search = Yii::$app->request->get('q');

// String-built SQL — a value like `' OR '1'='1` breaks out of the intended query
$customers = Customer::findBySql(
    "SELECT * FROM customer WHERE name LIKE '%$search%'"
)->all();
```

## Good Example

```php
<?php
$search = Yii::$app->request->get('q');

// Active Record / Query Builder — bound automatically
$customers = Customer::find()
    ->where(['like', 'name', $search])
    ->all();

// If raw SQL is genuinely required, bind parameters explicitly — never interpolate
$customers = Customer::findBySql(
    'SELECT * FROM customer WHERE name LIKE :search',
    [':search' => "%$search%"]
)->all();
```

## Why

- **Binding, not string safety tricks**: parameter binding sends the value separately from the SQL structure, so it can never be interpreted as SQL regardless of its content.
- **Applies equally to Active Record and Query Builder**, since `ActiveQuery` extends `Query` and shares the same `where()` binding behavior.
- **`findBySql()`/raw `createCommand()` are escape hatches**: they exist for cases the builder can't express, and *must* use bound `:param` placeholders, never `"...$var..."`.

Reference: [Security Best Practices Guide — Preventing SQL Injections](https://www.yiiframework.com/doc/guide/2.0/en/security-best-practices)
