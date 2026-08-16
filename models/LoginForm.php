<?php

declare(strict_types=1);

namespace app\models;

use yii\base\Model;

/**
 * LoginForm is the model behind the login form.
 */
class LoginForm extends Model
{
    public string $username = '';
    public string $password = '';
    public bool $rememberMe = true;

    /**
     * @return array the validation rules.
     */
    public function rules(): array
    {
        return [
            // username and password are both required
            [['username', 'password'], 'required'],
            // rememberMe must be a boolean value
            ['rememberMe', 'boolean'],
        ];
    }
}
