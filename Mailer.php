<?php

namespace chipmob\user;

use chipmob\user\models\User;
use Yii;
use yii\base\Component;
use yii\helpers\ArrayHelper;

/**
 * Class Mailer.
 *
 * @property string $viewPath The directory that contains the view files for composing mail messages
 *
 * @property string $welcomeSubject
 * @property string $newPasswordSubject
 * @property string $confirmationSubject
 * @property string $recoverySubject
 */
class Mailer extends Component
{
    const DEFAULT_SENDER = 'no-reply@web.loc';

    public string $viewPath = '@user/views/mail';
    public string $welcomeSubject;
    public string $newPasswordSubject;
    public string $confirmationSubject;
    public string $recoverySubject;

    /** @inheritdoc */
    public function __construct($config = [])
    {
        $this->welcomeSubject = Yii::t('user', 'Welcome to {0}', Yii::$app->name);
        $this->newPasswordSubject = Yii::t('user', 'Your password on {0} has been changed', Yii::$app->name);
        $this->confirmationSubject = Yii::t('user', 'Confirm account on {0}', Yii::$app->name);
        $this->recoverySubject = Yii::t('user', 'Complete password reset on {0}', Yii::$app->name);

        parent::__construct($config);
    }

    public function sendWelcomeMessage(User $user, array $params = []): bool
    {
        return $this->sendMessage($user->email, $this->welcomeSubject, 'welcome', compact('user', 'params'));
    }

    public function sendGeneratedPassword(User $user, array $params = []): bool
    {
        return $this->sendMessage($user->email, $this->newPasswordSubject, 'new_password', compact('user', 'params'));
    }

    public function sendConfirmationMessage(User $user, array $params = []): bool
    {
        return $this->sendMessage($user->email, $this->confirmationSubject, 'confirmation', compact('user', 'params'));
    }

    public function sendRecoveryMessage(User $user, array $params = []): bool
    {
        return $this->sendMessage($user->email, $this->recoverySubject, 'recovery', compact('user', 'params'));
    }

    protected function sendMessage(string $to, string $subject, string $view, array $params = []): bool
    {
        Yii::$app->mailer->viewPath = $this->viewPath;
        Yii::$app->mailer->view->theme = Yii::$app->view->theme;

        $sender = ArrayHelper::getValue(Yii::$app->params, 'noReplyEmail', self::DEFAULT_SENDER);

        if (!YII_DEBUG && $sender === self::DEFAULT_SENDER) Yii::warning(Yii::t('user', 'Please set variable "noReplyEmail" in params, otherwise <{0}> will be used', self::DEFAULT_SENDER));

        return Yii::$app->mailer
            ->compose(['html' => $view, 'text' => 'text/' . $view], $params)
            ->setTo($to)
            ->setFrom($sender)
            ->setSubject($subject)
            ->send();
    }
}
