<?php

declare(strict_types=1);

namespace app\services;

use app\models\LoginForm;
use app\models\User;
use Yii;
use yii\base\Security;

/**
 * Authenticates against the separate `adminUser` component (config/web.php),
 * independent of the storefront `user` session — logging into the shop never
 * grants admin access, and this never grants storefront access either.
 */
final class AdminLoginService
{
    public function __construct(
        private readonly Security $security,
    ) {
    }

    public function login(LoginForm $form): bool
    {
        if (!$form->validate()) {
            return false;
        }

        $user = User::findByUsername($form->username);

        if ($user === null || !$this->security->validatePassword($form->password, $user->password_hash)) {
            $form->addError('password', 'Incorrect username or password.');

            return false;
        }

        if (!Yii::$app->authManager->checkAccess($user->id, 'admin')) {
            $form->addError('password', 'This account does not have admin access.');

            return false;
        }

        return Yii::$app->adminUser->login($user, $form->rememberMe ? 3600 * 24 * 30 : 0);
    }
}
