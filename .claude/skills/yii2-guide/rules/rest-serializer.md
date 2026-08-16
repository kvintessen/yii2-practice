---
title: Whitelist Serialized Fields, Don't Rely on Defaults
impact: CRITICAL
impactDescription: Prevents leaking password hashes, tokens, and auth keys through the API
tags: rest, serializer, fields, sensitive-data
---

## Whitelist Serialized Fields, Don't Rely on Defaults

**Impact: CRITICAL**

By default, `fields()` on an Active Record resource returns *every* attribute — including `password_hash`, `auth_key`, and reset tokens if they're columns on the model. Override `fields()` to explicitly whitelist what the API exposes, and use `extraFields()` for optional expandable data.

## Bad Example

```php
<?php
class User extends \yii\db\ActiveRecord
{
    // No fields() override — GET /users/1 returns password_hash, auth_key,
    // password_reset_token, and every other column verbatim
}
```

## Good Example

```php
<?php
class User extends \yii\db\ActiveRecord
{
    public function fields()
    {
        $fields = parent::fields();
        unset($fields['auth_key'], $fields['password_hash'], $fields['password_reset_token']);
        return $fields;
    }

    public function extraFields()
    {
        return ['profile']; // only included when the client requests ?expand=profile
    }
}
```

## Why

- **Default is "expose everything"**: a new sensitive column added later (a token, an internal flag) is exposed automatically unless `fields()` explicitly excludes it — treat `fields()` as a required review point whenever the schema changes.
- **`extraFields()` keeps default payloads small**: expensive or rarely-needed relations only load when a client opts in via `?expand=`.
- **Centralized in the model**: every controller/action serializing this resource gets the same safe output, not just the ones someone remembered to filter.

Reference: [REST Resources Guide](https://www.yiiframework.com/doc/guide/2.0/en/rest-resources)
