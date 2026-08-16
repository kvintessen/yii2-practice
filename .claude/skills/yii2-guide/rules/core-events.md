---
title: Decouple Side Effects with Events
impact: MEDIUM
impactDescription: Keeps unrelated concerns (logging, notifications) out of core model/service logic
tags: core, events, decoupling, activerecord
---

## Decouple Side Effects with Events

**Impact: MEDIUM**

Any class extending `yii\base\Component` can declare and trigger events with `trigger()`, and other code can attach handlers with `on()` — either on a specific instance or, for `ActiveRecord`, on the whole class via the static `Event::on()`. Use events to keep side effects (notifications, logging, cache invalidation) out of the core class.

## Bad Example

```php
<?php
class Order extends \yii\db\ActiveRecord
{
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if ($insert) {
            // Order model now knows about email sending and Slack notifications —
            // unrelated concerns hard-wired into the persistence class
            Yii::$app->mailer->compose('order-confirmation', ['order' => $this])->send();
            Yii::$app->slack->notify("New order #{$this->id}");
        }
    }
}
```

## Good Example

```php
<?php
class Order extends \yii\db\ActiveRecord
{
    // Order stays focused on persistence; EVENT_AFTER_INSERT is inherited from ActiveRecord
}

// bootstrap.php or a module's bootstrap()
Event::on(Order::class, Order::EVENT_AFTER_INSERT, function ($event) {
    Yii::$app->mailer->compose('order-confirmation', ['order' => $event->sender])->send();
});

Event::on(Order::class, Order::EVENT_AFTER_INSERT, function ($event) {
    Yii::$app->slack->notify("New order #{$event->sender->id}");
});
```

## Why

- **Single Responsibility**: `Order` no longer needs to know about mailers or Slack; each concern is attached independently.
- **Add/remove behavior without touching the model**: New listeners can be registered elsewhere without editing `Order`.
- **Matches framework idioms**: `ActiveRecord` already exposes `EVENT_BEFORE_INSERT`, `EVENT_AFTER_UPDATE`, etc. for exactly this purpose.

Reference: [Events Guide](https://www.yiiframework.com/doc/guide/2.0/en/concept-events)
