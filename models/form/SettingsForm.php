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
 * SettingsForm gets user's username, email and password and changes them.
 *
 * @property-read Module $module
 */
class SettingsForm extends Model
{
    use ModuleTrait;
    use UserTrait;

    public string $access_token;
    public string $email;
    public string $username;
    public ?string $password = null;
    public ?string $current_password = null;

    /** @inheritdoc */
    public function init()
    {
        parent::init();

        $this->setAttributes([
            'access_token' => $this->user->access_token,
            'email' => $this->user->email,
            'username' => $this->user->username,
        ], false);
    }

    /** @inheritdoc */
    public function formName()
    {
        return 'settings-form';
    }

    /** @inheritdoc */
    public function rules()
    {
        return [
            [['username'], 'trim'],
            [['username'], 'required'],
            [['username'], 'string', 'min' => User::$minLoginLength, 'max' => 255],
            ['username', 'match', 'pattern' => User::$usernameRegexp],
            [['username'], 'unique', 'when' => fn(self $model, string $attribute) => $this->user->$attribute != $model->$attribute, 'targetClass' => User::class],
            ['password', 'string', 'min' => User::$minPasswordLength, 'max' => 72],
            ['password', 'match', 'pattern' => User::$passwordRegexp],
            ['current_password', 'required'],
            ['current_password', 'validatePassword'],
        ];
    }

    /** @inheritdoc */
    public function attributeLabels()
    {
        return [
            'access_token' => Yii::t('user', 'API key'),
            'email' => Yii::t('user', 'Email'),
            'username' => Yii::t('user', 'Username'),
            'password' => Yii::t('user', 'New password'),
            'current_password' => Yii::t('user', 'Current password'),
        ];
    }

    public function validatePassword(string $attribute)
    {
        if (!Password::validate($this->$attribute, $this->user->password_hash)) {
            $this->addError($attribute, Yii::t('user', 'Current password is not valid'));
        }
    }

    public function update(): bool
    {
        $this->user->setScenario(User::SCENARIO_SETTINGS);
        $this->user->setAttributes($this->attributes);
        return $this->user->save();
    }
}
