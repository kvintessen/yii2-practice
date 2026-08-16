<?php

declare(strict_types=1);

namespace app\tests\Unit\Models;

use app\models\SignupForm;

final class SignupFormTest extends \Codeception\Test\Unit
{
    public function testValidationWithEmptyCredentials()
    {
        $model = new SignupForm();

        verify($model->validate())->false();
        verify($model->errors)->arrayHasKey('username');
        verify($model->errors)->arrayHasKey('email');
        verify($model->errors)->arrayHasKey('password');
    }

    public function testValidationWithMismatchedPasswords()
    {
        $model = new SignupForm([
            'username' => 'new_user',
            'email' => 'new_user@example.com',
            'password' => 'password1',
            'passwordRepeat' => 'password2',
        ]);

        verify($model->validate())->false();
        verify($model->errors)->arrayHasKey('passwordRepeat');
    }

    public function testValidationWithExistingUsername()
    {
        $model = new SignupForm([
            'username' => 'admin',
            'email' => 'another_admin@example.com',
            'password' => 'password1',
            'passwordRepeat' => 'password1',
        ]);

        verify($model->validate())->false();
        verify($model->errors)->arrayHasKey('username');
    }
}
