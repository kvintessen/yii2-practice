---
title: Transactional, Reversible Migrations
impact: CRITICAL
impactDescription: Prevents half-applied schema changes and undeployable rollbacks
tags: db, migrations, schema, safeup, safedown
---

## Transactional, Reversible Migrations

**Impact: CRITICAL**

Use `yii migrate/create` to generate a timestamped migration, and prefer `safeUp()`/`safeDown()` over `up()`/`down()` — the `safe*` variants run implicitly inside a transaction, so a failure mid-migration rolls back instead of leaving the schema half-changed. Use portable abstract types (`Schema::TYPE_PK`, `$this->string()`) instead of raw column DDL.

## Bad Example

```php
<?php
class m240601_120000_add_orders_table extends \yii\db\Migration
{
    public function up()
    {
        // Multiple DDL statements, no transaction: if the second fails,
        // the first is already committed and there's no clean rollback
        $this->execute('CREATE TABLE `order` (id INT PRIMARY KEY, total DECIMAL(10,2))');
        $this->execute('CREATE INDEX idx_total ON `order` (total)');
    }

    public function down()
    {
        $this->execute('DROP TABLE `order`');
    }
}
```

## Good Example

```php
<?php
class m240601_120000_add_orders_table extends \yii\db\Migration
{
    public function safeUp()
    {
        $this->createTable('{{%order}}', [
            'id' => $this->primaryKey(),
            'total' => $this->decimal(10, 2)->notNull(),
            'created_at' => $this->integer()->notNull(),
        ]);
        $this->createIndex('idx-order-total', '{{%order}}', 'total');
    }

    public function safeDown()
    {
        $this->dropTable('{{%order}}');
    }
}
```

## Why

- **Atomic**: `safeUp()`/`safeDown()` wrap all statements in a transaction — a failure partway through rolls back cleanly.
- **Portable**: `$this->primaryKey()`, `$this->decimal()`, etc. generate the correct DDL per database driver instead of hard-coding MySQL syntax.
- **Reverse order in `safeDown()`**: undo operations in the opposite order they were applied in `safeUp()`.

Reference: [Migrations Guide](https://www.yiiframework.com/doc/guide/2.0/en/db-migrations)
