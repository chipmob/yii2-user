<?php

namespace chipmob\user\controllers;

use chipmob\user\components\actions\TotpAction;
use chipmob\user\components\actions\LoginAction;
use chipmob\user\components\actions\LogoutAction;
use chipmob\user\components\traits\ModuleTrait;
use chipmob\user\models\Account;
use chipmob\user\models\User;
use Yii;
use yii\authclient\AuthAction;
use yii\authclient\ClientInterface;
use yii\base\Action;
use yii\filters\AccessControl;
use yii\filters\AccessRule;
use yii\filters\VerbFilter;
use yii\helpers\Url;
use yii\web\Controller;

/**
 * SecurityController manages user authentication process.
 */
class SecurityController extends Controller
{
    use ModuleTrait;

    /** @inheritdoc */
    public function actions()
    {
        return [
            'login' => LoginAction::class,
            'totp' => TotpAction::class,
            'logout' => LogoutAction::class,
            'auth' => [
                'class' => AuthAction::class,
                'successCallback' => Yii::$app->user->isGuest ? [$this, 'authenticate'] : [$this, 'connect'],
            ],
        ];
    }

    /** @inheritdoc */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['login', 'totp', 'auth'],
                        'roles' => ['?'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['logout', 'auth'],
                        'roles' => ['@'],
                    ],
                    [
                        'allow' => false,
                        'actions' => ['login', 'totp'],
                        'roles' => ['@'],
                        'denyCallback' => function (AccessRule $rule, Action $action) {
                            return $action->controller->goHome();
                        },
                    ],
                ],
            ],
        ];
    }

    /**
     * Tries to authenticate user via social network. If user has already used
     * this network's account, he will be logged in. Otherwise, it will try
     * to create new user account.
     *
     * @param ClientInterface $client
     */
    public function authenticate(ClientInterface $client)
    {
        $account = Yii::$container->get('AccountQuery')->byClient($client)->one();
        if (!$this->module->enableRegistration && (!($account instanceof Account) || empty($account->user))) {
            Yii::$app->session->setFlash('danger', Yii::t('user', 'Registration on this website is disabled'));
            $this->action->successUrl = Yii::$app->user->loginUrl;
            return;
        }

        $account ??= Account::create($client);

        if (($user = $account->user) instanceof User) {
            if ($user->isBlocked) {
                Yii::$app->session->setFlash('danger', Yii::t('user', 'Your account has been blocked'));
                $this->action->successUrl = Yii::$app->user->loginUrl;
            } elseif ($user->isRemoved) {
                Yii::$app->session->setFlash('danger', Yii::t('user', 'Your account has been removed'));
                $this->action->successUrl = Yii::$app->user->loginUrl;
            } elseif ($user->isTotp) {
                Yii::$app->session->set(User::OAUTH_SESSION_KEY, $user->id);
                Yii::$app->session->set(User::TOTP_SESSION_KEY, $user->id);
                $this->action->successUrl = Url::to(['/user/security/totp']);
            } else {
                $user->confirm();
                Yii::$app->session->set(User::OAUTH_SESSION_KEY, $user->id);
                Yii::$app->user->login($user, $this->module->loginLifetime);
                $this->action->successUrl = Yii::$app->user->returnUrl;
            }
        } else {
            Yii::$app->session->set(Account::CONNECT_SESSION_KEY, $account->id);
            $this->action->successUrl = Url::to(['/user/registration/connect']);
        }
    }

    /**
     * Tries to connect social account to user.
     *
     * @param ClientInterface $client
     */
    public function connect(ClientInterface $client)
    {
        Account::connect($client);
        $this->action->successUrl = Url::to(['/user/settings/networks']);
    }
}
