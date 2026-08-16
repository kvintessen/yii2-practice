---
title: Implement IdentityInterface Fully, Don't Roll Your Own Session Auth
impact: CRITICAL
impactDescription: Avoids reinventing session/token handling with weaker guarantees
tags: sec, authentication, identityinterface, user-component
---

## Implement IdentityInterface Fully, Don't Roll Your Own Session Auth

**Impact: CRITICAL**

Configure the `user` component with an `identityClass` implementing `yii\web\IdentityInterface` (`findIdentity()`, `findIdentityByAccessToken()`, `getId()`, `getAuthKey()`, `validateAuthKey()`), then log in/out via `Yii::$app->user->login()`/`logout()`. Hand-rolled `$_SESSION['user_id'] = ...` auth skips the auth-key validation the framework uses to guard cookie-based auto-login.

## Bad Example

```php
<?php
// Reinvents session auth, bypasses IdentityInterface entirely
public function actionLogin()
{
    $user = User::findOne(['username' => Yii::$app->request->post('username')]);
    if ($user && password_verify(Yii::$app->request->post('password'), $user->password_hash)) {
        $_SESSION['user_id'] = $user->id; // no auth-key check, no framework session regeneration
    }
}
```

## Good Example

```php
<?php
class User extends \yii\db\ActiveRecord implements \yii\web\IdentityInterface
{
    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['access_token' => $token]);
    }

    public function getId()
    {
        return $this->id;
    }

    public function getAuthKey()
    {
        return $this->auth_key;
    }

    public function validateAuthKey($authKey)
    {
        return $this->auth_key === $authKey;
    }
}
```

```php
<?php
// config/web.php
'components' => [
    'user' => [
        'identityClass' => 'app\models\User',
    ],
],
```

```php
<?php
public function actionLogin()
{
    $model = new LoginForm();
    if ($model->load(Yii::$app->request->post()) && $model->login()) {
        // LoginForm::login() calls Yii::$app->user->login($this->getUser())
        return $this->goBack();
    }
}
```

## Why

- **`auth_key` guards auto-login cookies**: framework-managed login validates this key, protecting cookie-based "remember me" against tampering — a hand-rolled `$_SESSION` check has no equivalent.
- **Consistent access everywhere**: `Yii::$app->user->identity`, `->id`, `->isGuest` work anywhere in the app once `IdentityInterface` is implemented, instead of ad hoc `$_SESSION` reads.
- **Token auth for APIs comes for free**: `findIdentityByAccessToken()` lets the same identity class back both session-based web auth and stateless API auth.

Reference: [Authentication Guide](https://www.yiiframework.com/doc/guide/2.0/en/security-authentication)
