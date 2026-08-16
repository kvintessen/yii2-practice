<?php

declare(strict_types=1);

namespace app\services;

use app\models\SignupForm;
use app\models\User;
use yii\base\Security;

final class SignupService
{
    public function __construct(private readonly Security $security)
    {
    }

    public function signup(SignupForm $form): User|null
    {
        if (!$form->validate()) {
            return null;
        }

        $user = new User();
        $user->username = $form->username;
        $user->email = $form->email;
        $user->password_hash = $this->security->generatePasswordHash($form->password);
        $user->generateAuthKey();

        return $user->save() ? $user : null;
    }
}
