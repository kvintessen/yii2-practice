---
title: Hash Passwords with the Security Component, Never MD5/SHA1
impact: CRITICAL
impactDescription: Uses bcrypt, resilient against brute-force/rainbow-table attacks
tags: sec, passwords, hashing, security
---

## Hash Passwords with the Security Component, Never MD5/SHA1

**Impact: CRITICAL**

Use `Yii::$app->security->generatePasswordHash()` (bcrypt) to hash passwords at registration/change time, and `validatePassword()` to verify at login. `md5()`/`sha1()` are fast general-purpose hashes, not designed to resist brute-force password cracking.

## Bad Example

```php
<?php
$user->password_hash = md5($password); // crackable at massive scale with modern hardware

// login
if (md5($input) === $user->password_hash) {
    Yii::$app->user->login($user);
}
```

## Good Example

```php
<?php
// registration
$user->password_hash = Yii::$app->security->generatePasswordHash($password);
$user->save();

// login
if (Yii::$app->security->validatePassword($input, $user->password_hash)) {
    Yii::$app->user->login($user);
}
```

## Why

- **Bcrypt is deliberately slow**: it's tunable and resists brute-force/rainbow-table attacks in a way general-purpose hashes like MD5/SHA1 don't.
- **Salting handled automatically**: `generatePasswordHash()` embeds a random salt in the resulting hash string — no manual salt management needed.
- **One call each way**: `generatePasswordHash()`/`validatePassword()` are the only two functions needed; there's no reason to hand-roll hashing.

Reference: [Security Guide — Password Hashing](https://www.yiiframework.com/doc/guide/2.0/en/security-passwords)
