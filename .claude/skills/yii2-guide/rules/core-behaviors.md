---
title: Use Behaviors Instead of Duplicating Cross-Cutting Logic
impact: MEDIUM
impactDescription: Removes copy-pasted lifecycle code from models
tags: core, behaviors, activerecord, timestampbehavior
---

## Use Behaviors Instead of Duplicating Cross-Cutting Logic

**Impact: MEDIUM**

A `Behavior` mixes methods/event handlers into a component without inheritance. Attach reusable cross-cutting logic (timestamps, blame columns, slugs) via `behaviors()` instead of overriding lifecycle methods like `beforeSave()` in every model.

## Bad Example

```php
<?php
class Post extends \yii\db\ActiveRecord
{
    public function beforeSave($insert)
    {
        if ($insert) {
            $this->created_at = time();
        }
        $this->updated_at = time(); // repeated in every model that needs timestamps
        return parent::beforeSave($insert);
    }
}
```

## Good Example

```php
<?php
use yii\behaviors\TimestampBehavior;

class Post extends \yii\db\ActiveRecord
{
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'attributes' => [
                    self::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
                    self::EVENT_BEFORE_UPDATE => ['updated_at'],
                ],
            ],
        ];
    }
}
```

## Why

- **No copy-pasted lifecycle overrides**: The same behavior config is reused across every model needing timestamps, blame columns, etc.
- **Composable**: Multiple behaviors can be attached to one component without any inheritance conflicts.
- **Detachable**: Behaviors can be attached/detached dynamically at runtime via `attachBehavior()`/`detachBehavior()` when needed.

Reference: [Behaviors Guide](https://www.yiiframework.com/doc/guide/2.0/en/concept-behaviors)
