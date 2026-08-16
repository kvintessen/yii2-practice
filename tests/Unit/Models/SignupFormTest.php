<?php

declare(strict_types=1);

namespace app\tests\Unit\Models;

use app\models\SignupForm;
use app\models\User;
use yii\base\Security;

final class SignupFormTest extends \Codeception\Test\Unit
{
    public function testSignupWithEmptyCredentials()
    {
        $model = new SignupForm(new Security());

        verify($model->signup())->null();
    }

    public function testSignupWithMismatchedPasswords()
    {
        $model = new SignupForm(
            new Security(),
            [
                'username' => 'new_user',
                'email' => 'new_user@example.com',
                'password' => 'password1',
                'passwordRepeat' => 'password2',
            ],
        );

        verify($model->signup())->null();
        verify($model->errors)->arrayHasKey('passwordRepeat');
    }

    public function testSignupWithExistingUsername()
    {
        $model = new SignupForm(
            new Security(),
            [
                'username' => 'admin',
                'email' => 'another_admin@example.com',
                'password' => 'password1',
                'passwordRepeat' => 'password1',
            ],
        );

        verify($model->signup())->null();
        verify($model->errors)->arrayHasKey('username');
    }

    public function testSignupCorrect()
    {
        $model = new SignupForm(
            new Security(),
            [
                'username' => 'new_user',
                'email' => 'new_user@example.com',
                'password' => 'password1',
                'passwordRepeat' => 'password1',
            ],
        );

        $user = $model->signup();

        verify($user)->instanceOf(User::class);
        verify($user->username)->equals('new_user');
        verify($user->email)->equals('new_user@example.com');
        verify($user->auth_key)->notEmpty();
        verify((new Security())->validatePassword('password1', $user->password_hash))->true();
    }
}
