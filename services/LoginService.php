<?php

declare(strict_types=1);

namespace app\services;

use app\models\LoginForm;
use app\models\User;
use Yii;
use yii\base\Security;

final class LoginService
{
    public function __construct(private readonly Security $security)
    {
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

        return Yii::$app->user->login($user, $form->rememberMe ? 3600 * 24 * 30 : 0);
    }
}
