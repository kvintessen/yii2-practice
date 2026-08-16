<?php

declare(strict_types=1);

namespace app\tests\Unit\Services;

use app\models\SignupForm;
use app\models\User;
use app\services\CartMergeService;
use app\services\SignupService;
use app\tests\Support\Fixtures\UserFixture;
use yii\base\Security;

final class SignupServiceTest extends \Codeception\Test\Unit
{
    private SignupService $_service;

    public function _fixtures(): array
    {
        return ['users' => UserFixture::class];
    }

    protected function _before()
    {
        $this->_service = new SignupService(new Security(), new CartMergeService());
    }

    public function testSignupWithEmptyCredentials()
    {
        $model = new SignupForm();

        verify($this->_service->signup($model))->null();
    }

    public function testSignupWithMismatchedPasswords()
    {
        $model = new SignupForm([
            'username' => 'new_user',
            'email' => 'new_user@example.com',
            'password' => 'password1',
            'passwordRepeat' => 'password2',
        ]);

        verify($this->_service->signup($model))->null();
        verify($model->errors)->arrayHasKey('passwordRepeat');
    }

    public function testSignupWithExistingUsername()
    {
        $model = new SignupForm([
            'username' => 'admin',
            'email' => 'another_admin@example.com',
            'password' => 'password1',
            'passwordRepeat' => 'password1',
        ]);

        verify($this->_service->signup($model))->null();
        verify($model->errors)->arrayHasKey('username');
    }

    public function testSignupCorrect()
    {
        $model = new SignupForm([
            'username' => 'new_user',
            'email' => 'new_user@example.com',
            'password' => 'password1',
            'passwordRepeat' => 'password1',
        ]);

        $user = $this->_service->signup($model);

        verify($user)->instanceOf(User::class);
        verify($user->username)->equals('new_user');
        verify($user->email)->equals('new_user@example.com');
        verify($user->auth_key)->notEmpty();
        verify((new Security())->validatePassword('password1', $user->password_hash))->true();
    }
}
