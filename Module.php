<?php

namespace chipmob\user;

/**
 * User module.
 *
 * @property string $enableRegistration Whether to enable registration
 * @property string $enableGeneratingPassword Whether to remove password field from registration form
 * @property string $enableConfirmation Whether user has to confirm his account
 * @property string $enableUnconfirmedLogin Whether to allow logging in without confirmation
 * @property string $enablePasswordRecovery Whether to enable password recovery
 * @property string $enableAccountDelete Whether user can remove his account
 * @property string $enableImpersonateUser Enable the 'impersonate as another user' function
 *
 * @property int $rememberFor The time you want the user will be remembered without asking for credentials
 * @property int $loginLifetime Время жизни сессии для login
 * @property int $switchUserLifetime Время жизни сессии для switch
 * @property int $confirmWithin The time before a confirmation token becomes invalid
 * @property int $recoverWithin The time before a recovery token becomes invalid
 *
 * @property int $cost  Cost parameter used by the Blowfish hash algorithm
 * @property string $adminPermission The Administrator permission name
 * @property array $mailerConfig Mailer configuration
 * @property array $modelMap Model map
 *
 * @property string $urlPrefix The prefix for user module URL.
 * @see [[GroupUrlRule::prefix]]
 *
 * @property array $urlRules The rules to be used in URL management
 */
class Module extends \yii\base\Module
{
    public bool $enableRegistration       = true;
    public bool $enableGeneratingPassword = false;
    public bool $enableConfirmation       = true;
    public bool $enableUnconfirmedLogin   = false;
    public bool $enablePasswordRecovery   = true;
    public bool $enableAccountDelete      = false;
    public bool $enableImpersonateUser    = true;

    public int $rememberFor = 2 * 7 * 24 * 60 * 60; // two weeks
    public int $loginLifetime = 1 * 60 * 60; // 1 hour
    public int $switchUserLifetime = 1 * 60 * 60; // 1 hour
    public int $confirmWithin = 24 * 60 * 60; // 24 hours
    public int $recoverWithin = 6 * 60 * 60; // 6 hours

    public int $cost = 13;

    public ?string $adminPermission = null;

    public array $mailerConfig = [];
    public array $modelMap = [];

    public string $urlPrefix = 'user';
    public array $urlRules = [
        '<action:(login|logout|totp|auth)>'       => 'security/<action>',
        '<action:(register|connect|resend)>'      => 'registration/<action>',
        'confirm/<id:\d+>/<code:[A-Za-z0-9_-]+>'  => 'registration/confirm',
        'forgot'                                  => 'recovery/request',
        'recover/<id:\d+>/<code:[A-Za-z0-9_-]+>'  => 'recovery/reset',
        'settings/<action:\w+>'                   => 'settings/<action>',
    ];
}
