---
title: Wrap Multi-Step Writes in a Transaction
impact: CRITICAL
impactDescription: Prevents partially-committed, inconsistent data on failure
tags: ar, transactions, consistency
---

## Wrap Multi-Step Writes in a Transaction

**Impact: CRITICAL**

When an operation touches more than one Active Record (or more than one table), wrap the writes in a transaction so a failure partway through rolls back everything instead of leaving the database in an inconsistent state.

## Bad Example

```php
<?php
// If $order->save() succeeds but decrementing stock throws, the order
// exists with no matching stock deduction — data is now inconsistent
$order = new Order($orderData);
$order->save();

foreach ($order->items as $item) {
    $product = Product::findOne($item->product_id);
    $product->stock -= $item->quantity;
    $product->save(); // may fail on a later iteration
}
```

## Good Example

```php
<?php
Yii::$app->db->transaction(function ($db) use ($orderData) {
    $order = new Order($orderData);
    if (!$order->save()) {
        throw new \yii\base\Exception('Could not save order');
    }

    foreach ($order->items as $item) {
        $product = Product::findOne($item->product_id);
        $product->stock -= $item->quantity;
        if (!$product->save()) {
            throw new \yii\base\Exception('Could not update stock');
        }
    }
});
```

## Why

- **All-or-nothing**: any exception thrown inside the closure triggers an automatic rollback of everything done so far.
- **No manual begin/commit bookkeeping**: `transaction(callable)` handles `beginTransaction()`/`commit()`/`rollBack()` for you.
- **For per-model control**, declare a `transactions()` method on the AR class to mark which scenarios always run inside a transaction.

Reference: [Active Record Guide — Transactions](https://www.yiiframework.com/doc/guide/2.0/en/db-active-record)
