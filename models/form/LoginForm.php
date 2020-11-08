<?php

namespace chipmob\user\models\form;

use chipmob\user\components\helpers\Password;
use chipmob\user\components\traits\ModuleTrait;
use chipmob\user\components\traits\UserTrait;
use chipmob\user\models\User;
use chipmob\user\Module;
use Yii;
use yii\base\Model;

/**
 * LoginForm get user's login and password, validates them and logs the user in. If user has been blocked, it adds
 * an error to login form.
 *
 * @property-read Module $module
 */
class LoginForm extends Model
{
    use ModuleTrait;
    use UserTrait;

    public ?string $login = null;
    public ?string $password = null;
    public ?string $rememberMe = null;

    /** @inheritdoc */
    public function formName()
    {
        return 'login-form';
    }

    /** @inheritdoc */
    public function rules()
    {
        return [
            ['login', 'trim'],
            [['login', 'password'], 'required'],
            ['login', 'validateLogin'],
            ['password', 'validatePassword'],
            ['rememberMe', 'boolean'],
        ];
    }

    /** @inheritdoc */
    public function attributeLabels()
    {
        return [
            'login' => Yii::t('user', 'Login'),
            'password' => Yii::t('user', 'Password'),
            'rememberMe' => Yii::t('user', 'Remember me next time'),
        ];
    }

    public function validateLogin(string $attribute)
    {
        if ($this->module->enableConfirmation && !$this->module->enableUnconfirmedLogin && !$this->user->isConfirmed) {
            $this->addError($attribute, Yii::t('user', 'You need to confirm your email address'));
        }
        if ($this->user->isBlocked) {
            $this->addError($attribute, Yii::t('user', 'Your account has been blocked'));
        }
        if ($this->user->isRemoved) {
            $this->addError($attribute, Yii::t('user', 'Your account has been removed'));
        }
    }

    public function validatePassword(string $attribute)
    {
        if (!Password::validate($this->password, $this->user->password_hash)) {
            $this->addError($attribute, Yii::t('user', 'Invalid login or password'));
        }
    }

    /** @inheritdoc */
    public function beforeValidate()
    {
        if (parent::beforeValidate()) {
            $user = Yii::$container->get('UserQuery')->byUsernameOrEmail(trim($this->login))->one();
            if ($user instanceof User) {
                $this->user = $user;
                return true;
            } else {
                $this->addError('password', Yii::t('user', 'Invalid login or password'));
                return false;
            }
        } else {
            return false;
        }
    }
}
