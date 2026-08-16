<?php

declare(strict_types=1);

namespace app\services;

use app\models\LoginForm;
use app\models\User;
use Yii;
use yii\base\Security;

final class LoginService
{
    public function __construct(
        private readonly Security $security,
        private readonly CartMergeService $cartMergeService,
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

        // Captured before login() regenerates the session id.
        $guestSessionId = Yii::$app->session->getId();

        if (!Yii::$app->user->login($user, $form->rememberMe ? 3600 * 24 * 30 : 0)) {
            return false;
        }

        $this->cartMergeService->mergeGuestCartIntoUser($guestSessionId, $user->id);

        return true;
    }
}
