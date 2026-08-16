<?php

declare(strict_types=1);

namespace app\models;

use yii\base\Model;

/**
 * SignupForm is the model behind the registration form.
 */
class SignupForm extends Model
{
    public string $username = '';
    public string $email = '';
    public string $password = '';
    public string $passwordRepeat = '';

    /**
     * @return array the validation rules.
     */
    public function rules(): array
    {
        return [
            [['username', 'email', 'password', 'passwordRepeat'], 'required'],

            ['username', 'string', 'min' => 2, 'max' => 255],
            ['username', 'match', 'pattern' => '/^[a-zA-Z0-9_-]+$/', 'message' => 'Username may only contain letters, numbers, dashes, and underscores.'],
            ['username', 'unique', 'targetClass' => User::class, 'message' => 'This username has already been taken.'],

            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            ['email', 'unique', 'targetClass' => User::class, 'message' => 'This email address has already been taken.'],

            ['password', 'string', 'min' => 8],
            ['passwordRepeat', 'compare', 'compareAttribute' => 'password', 'message' => 'Passwords do not match.'],
        ];
    }
}
