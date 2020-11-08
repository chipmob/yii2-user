<?php

namespace chipmob\user\controllers;

use chipmob\user\components\actions\ConfirmAction;
use chipmob\user\components\actions\ConnectAction;
use chipmob\user\components\actions\RegisterAction;
use chipmob\user\components\actions\ResendAction;
use yii\base\Action;
use yii\filters\AccessControl;
use yii\filters\AccessRule;
use yii\web\Controller;

/**
 * RegistrationController is responsible for all registration process, which includes registration of a new account,
 * resending confirmation tokens, email confirmation and registration via social networks.
 */
class RegistrationController extends Controller
{
    /** @inheritdoc */
    public function actions()
    {
        return [
            'register' => RegisterAction::class,
            'connect' => ConnectAction::class,
            'confirm' => ConfirmAction::class,
            'resend' => ResendAction::class,
        ];
    }

    /** @inheritdoc */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['register', 'connect'],
                        'roles' => ['?'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['confirm', 'resend'],
                        'roles' => ['?', '@'],
                    ],
                    [
                        'allow' => false,
                        'actions' => ['register', 'connect'],
                        'roles' => ['@'],
                        'denyCallback' => function (AccessRule $rule, Action $action) {
                            return $action->controller->goHome();
                        },
                    ],
                ],
            ],
        ];
    }
}
