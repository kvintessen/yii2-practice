---
title: Query Builder with Bound Parameters, Not Raw SQL Concatenation
impact: CRITICAL
impactDescription: Primary defense against SQL injection when Active Record isn't a fit
tags: db, query-builder, sql-injection, security
---

## Query Builder with Bound Parameters, Not Raw SQL Concatenation

**Impact: CRITICAL**

`yii\db\Query` builds SQL programmatically and automatically binds parameters in hash-format and operator-format `where()` conditions. Never interpolate user input directly into a SQL string.

## Bad Example

```php
<?php
$status = Yii::$app->request->get('status');
// User input concatenated straight into SQL — classic SQL injection
$rows = Yii::$app->db
    ->createCommand("SELECT * FROM user WHERE status=$status")
    ->queryAll();
```

## Good Example

```php
<?php
$status = Yii::$app->request->get('status');

// Hash format — auto-bound
$rows = (new \yii\db\Query())
    ->select(['id', 'email'])
    ->from('user')
    ->where(['status' => $status])
    ->all();

// Or explicit binding when the condition needs to be a raw string
$rows = (new \yii\db\Query())
    ->from('user')
    ->where('status = :status', [':status' => $status])
    ->all();
```

## Why

- **No injection surface**: Hash/operator `where()` formats and explicit `params`/`addParams()` route every value through PDO parameter binding.
- **Readable conditions**: `['like', 'name', $term]`, `['between', 'id', 1, 10]` etc. express intent without manual SQL string assembly.
- **Same guarantee applies to Active Record**, since `ActiveQuery` extends `Query`.

Reference: [Query Builder Guide](https://www.yiiframework.com/doc/guide/2.0/en/db-query-builder)
