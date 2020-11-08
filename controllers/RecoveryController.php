<?php

namespace chipmob\user\controllers;

use chipmob\user\components\actions\RequestAction;
use chipmob\user\components\actions\ResetAction;
use yii\base\Action;
use yii\filters\AccessControl;
use yii\filters\AccessRule;
use yii\web\Controller;

/**
 * RecoveryController manages password recovery process.
 */
class RecoveryController extends Controller
{
    /** @inheritdoc */
    public function actions()
    {
        return [
            'request' => RequestAction::class,
            'reset' => ResetAction::class,
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
                        'actions' => ['request', 'reset'],
                        'roles' => ['?'],
                    ],
                    [
                        'allow' => false,
                        'actions' => ['request', 'reset'],
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
