<?php

namespace chipmob\user\models\form;

use chipmob\user\components\traits\ModuleTrait;
use chipmob\user\models\User;
use chipmob\user\Module;
use Yii;
use yii\base\Model;

/**
 * Registration form collects user input on registration process, validates it and creates new User model.
 *
 * @property-read Module $module
 */
class RegistrationForm extends Model
{
    use ModuleTrait;

    public ?string $email = null;
    public ?string $username = null;
    public ?string $password = null;

    /** @inheritdoc */
    public function formName()
    {
        return 'register-form';
    }

    /** @inheritdoc */
    public function rules()
    {
        return [
            [['username', 'email'], 'trim'],
            [['username', 'email'], 'required'],
            [['username', 'email'], 'string', 'min' => User::$minLoginLength, 'max' => 255],
            ['username', 'match', 'pattern' => User::$usernameRegexp],
            ['email', 'email'],
            ['username', 'unique', 'targetClass' => User::class, 'message' => Yii::t('user', 'This username has already been taken')],
            ['email', 'unique', 'targetClass' => User::class, 'message' => Yii::t('user', 'This email address has already been taken')],
            ['password', 'required', 'skipOnEmpty' => $this->module->enableGeneratingPassword],
            ['password', 'string', 'min' => User::$minPasswordLength, 'max' => 72],
            ['password', 'match', 'pattern' => User::$passwordRegexp],
        ];
    }

    /** @inheritdoc */
    public function attributeLabels()
    {
        return [
            'email' => Yii::t('user', 'Email'),
            'username' => Yii::t('user', 'Username'),
            'password' => Yii::t('user', 'Password'),
        ];
    }

    public function register(): bool
    {
        $user = Yii::createObject([
            'class' => User::class,
            'scenario' => User::SCENARIO_REGISTER,
        ]);
        $user->setAttributes($this->attributes);
        return $user->create();
    }
}
