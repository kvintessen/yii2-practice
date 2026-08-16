<?php

declare(strict_types=1);

namespace app\models;

use Yii;
use yii\base\Model;
use yii\base\Security;

/**
 * SignupForm is the model behind the registration form.
 */
class SignupForm extends Model
{
    public string $username = '';
    public string $email = '';
    public string $password = '';
    public string $passwordRepeat = '';

    public function __construct(private readonly Security $security, $config = [])
    {
        parent::__construct($config);
    }

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

    /**
     * Signs the user up.
     *
     * @return User|null the saved user, or null if signup failed
     */
    public function signup(): User|null
    {
        if (!$this->validate()) {
            return null;
        }

        $user = new User();
        $user->username = $this->username;
        $user->email = $this->email;
        $user->password_hash = $this->security->generatePasswordHash($this->password);
        $user->generateAuthKey();

        return $user->save() ? $user : null;
    }
}
