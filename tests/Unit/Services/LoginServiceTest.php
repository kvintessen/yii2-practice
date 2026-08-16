<?php

declare(strict_types=1);

namespace app\tests\Unit\Services;

use app\models\LoginForm;
use app\services\LoginService;
use Yii;
use yii\base\Security;

final class LoginServiceTest extends \Codeception\Test\Unit
{
    private LoginService $_service;

    protected function _before()
    {
        $this->_service = new LoginService(new Security());
    }

    protected function _after()
    {
        Yii::$app->user->logout();
    }

    public function testLoginNoUser()
    {
        $model = new LoginForm([
            'username' => 'not_existing_username',
            'password' => 'not_existing_password',
        ]);

        verify($this->_service->login($model))->false();
        verify(Yii::$app->user->isGuest)->true();
    }

    public function testLoginWrongPassword()
    {
        $model = new LoginForm([
            'username' => 'demo',
            'password' => 'wrong_password',
        ]);

        verify($this->_service->login($model))->false();
        verify(Yii::$app->user->isGuest)->true();
        verify($model->errors)->arrayHasKey('password');
    }

    public function testLoginCorrect()
    {
        $model = new LoginForm([
            'username' => 'demo',
            'password' => 'demo',
        ]);

        verify($this->_service->login($model))->true();
        verify(Yii::$app->user->isGuest)->false();
        verify($model->errors)->arrayHasNotKey('password');
    }
}
