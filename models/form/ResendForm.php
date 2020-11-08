<?php

namespace chipmob\user\models\form;

use chipmob\user\Mailer;
use chipmob\user\models\Token;
use chipmob\user\models\User;
use Yii;
use yii\base\Model;

/**
 * ResendForm gets user email address and if user with given email is registered it sends new confirmation message
 * to him in case he did not validate his email.
 */
class ResendForm extends Model
{
    public ?string $email = null;

    /** @inheritdoc */
    public function formName()
    {
        return 'resend-form';
    }

    /** @inheritdoc */
    public function rules()
    {
        return [
            ['email', 'required'],
            ['email', 'email'],
        ];
    }

    /** @inheritdoc */
    public function attributeLabels()
    {
        return [
            'email' => Yii::t('user', 'Email'),
        ];
    }

    public function resend()
    {
        $user = Yii::$container->get('UserQuery')->active()->byEmail($this->email)->one();
        if ($user instanceof User && !$user->isConfirmed) {
            $token = $user->createToken(Token::TYPE_CONFIRMATION);
            Yii::$container->get(Mailer::class)->sendConfirmationMessage($user, ['token_url' => $token->url]);
        }
    }
}
