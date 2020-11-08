<?php

namespace chipmob\user\models\form;

use chipmob\user\components\traits\UserTrait;
use chipmob\user\Mailer;
use chipmob\user\models\Token;
use chipmob\user\models\User;
use Yii;
use yii\base\Model;

/**
 * Model for collecting data on password recovery.
 */
class RecoveryForm extends Model
{
    use UserTrait;

    const SCENARIO_REQUEST = 'request';
    const SCENARIO_RESET = 'reset';

    public ?string $email = null;
    public ?string $password = null;

    /** @inheritdoc */
    public function formName()
    {
        return 'recovery-form';
    }

    /** @inheritdoc */
    public function rules()
    {
        return [
            ['email', 'trim'],
            [['email', 'password'], 'required'],
            ['email', 'email'],
            ['password', 'string', 'min' => User::$minPasswordLength, 'max' => 72],
            ['password', 'match', 'pattern' => User::$passwordRegexp],
        ];
    }

    /** @inheritdoc */
    public function scenarios()
    {
        return [
            self::SCENARIO_REQUEST => ['email'],
            self::SCENARIO_RESET => ['password'],
        ];
    }

    /** @inheritdoc */
    public function attributeLabels()
    {
        return [
            'email' => Yii::t('user', 'Email'),
            'password' => Yii::t('user', 'Password'),
        ];
    }

    public function recover()
    {
        $user = Yii::$container->get('UserQuery')->active()->byEmail($this->email)->one();
        if ($user instanceof User) {
            $token = $user->createToken(Token::TYPE_RECOVERY);
            Yii::$container->get(Mailer::class)->sendRecoveryMessage($user, ['token_url' => $token->url]);
        }
    }

    public function reset()
    {
        $this->user->setAttributes($this->attributes);
        if ($this->user->resetPassword()) {
            Yii::$app->session->setFlash('success', Yii::t('user', 'Your password has been changed successfully'));
        } else {
            Yii::$app->session->setFlash('danger', Yii::t('user', 'An error occurred and your password has not been changed. Please try again later.'));
        }
    }
}
