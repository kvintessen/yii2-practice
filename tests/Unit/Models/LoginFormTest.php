<?php

declare(strict_types=1);

namespace app\tests\Unit\Models;

use app\models\LoginForm;

final class LoginFormTest extends \Codeception\Test\Unit
{
    public function testValidationWithEmptyCredentials()
    {
        $model = new LoginForm();

        verify($model->validate())->false();
        verify($model->errors)->arrayHasKey('username');
        verify($model->errors)->arrayHasKey('password');
    }

    public function testValidationWithCredentialsPresent()
    {
        $model = new LoginForm([
            'username' => 'demo',
            'password' => 'demo',
        ]);

        verify($model->validate())->true();
    }
}
