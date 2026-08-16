---
title: Eager-Load Relations to Avoid N+1 Queries
impact: CRITICAL
impactDescription: Turns O(n) queries into 2 queries for a list + its relation
tags: ar, relations, eager-loading, performance, n+1
---

## Eager-Load Relations to Avoid N+1 Queries

**Impact: CRITICAL**

Declare relations with `hasOne()`/`hasMany()`. Accessing a relation as a property (`$customer->orders`) triggers a query — fine for a single record, but inside a loop over many records it causes one query per row (N+1). Use `with()` to eager-load instead.

## Bad Example

```php
<?php
class Customer extends \yii\db\ActiveRecord
{
    public function getOrders()
    {
        return $this->hasMany(Order::class, ['customer_id' => 'id']);
    }
}

// 1 query for customers + 1 query PER customer for orders = 101 queries for 100 customers
$customers = Customer::find()->limit(100)->all();
foreach ($customers as $customer) {
    foreach ($customer->orders as $order) {
        // ...
    }
}
```

## Good Example

```php
<?php
// 2 queries total, regardless of how many customers are returned
$customers = Customer::find()
    ->with('orders')
    ->limit(100)
    ->all();

foreach ($customers as $customer) {
    foreach ($customer->orders as $order) { // no additional query
        // ...
    }
}
```

## Why

- **Predictable query count**: `with()` executes 2 queries total (parent + related) instead of 1-per-row.
- **Same result shape**: the relation is still accessed as `$customer->orders`; only the loading strategy changes.
- **Compose with custom query scopes** (see `ar-scopes`) for relation filtering without duplicating conditions.

Reference: [Active Record Guide — Relational Data](https://www.yiiframework.com/doc/guide/2.0/en/db-active-record)
