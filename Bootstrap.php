<?php

namespace chipmob\user;

use chipmob\user\models\Action;
use Yii;
use yii\base\BootstrapInterface;

class Bootstrap implements BootstrapInterface
{
    private array $_modelMap = [
        'Account' => 'chipmob\user\models\Account',
        'Action' => 'chipmob\user\models\Action',
        'Log' => 'chipmob\user\models\Log',
        'Profile' => 'chipmob\user\models\Profile',
        'Token' => 'chipmob\user\models\Token',
        'User' => 'chipmob\user\models\User',
        'GoogleTotpForm' => 'chipmob\user\models\form\GoogleTotpForm',
        'LoginForm' => 'chipmob\user\models\form\LoginForm',
        'RecoveryForm' => 'chipmob\user\models\form\RecoveryForm',
        'RegistrationForm' => 'chipmob\user\models\form\RegistrationForm',
        'ResendForm' => 'chipmob\user\models\form\ResendForm',
        'SettingsForm' => 'chipmob\user\models\form\SettingsForm',
        'TotpForm' => 'chipmob\user\models\form\TotpForm',
        'ActionSearch' => 'chipmob\user\models\search\ActionSearch',
        'UserSearch' => 'chipmob\user\models\search\UserSearch',
    ];

    /** @inheritdoc */
    public function bootstrap($app)
    {
        if (($module = $app->getModule('user')) instanceof Module) {
            Yii::setAlias('@user', __DIR__);
            $this->_modelMap = array_merge($this->_modelMap, $module->modelMap);
            foreach ($this->_modelMap as $name => $definition) {
                if (preg_match("/(Form|Search|Query)$/", $name, $match)) {
                    switch ($match[1]) {
                        case 'Form':
                            $folder = 'form\\';
                            break;
                        case 'Search':
                            $folder = 'search\\';
                            break;
                        case 'Query':
                            $folder = 'query\\';
                            break;
                        default:
                            $folder = '';
                    }
                    $class = "chipmob\\user\\models\\" . $folder . $name;
                } else {
                    $class = "chipmob\\user\\models\\" . $name;
                }
                Yii::$container->set($class, $definition);
                $modelClass = is_array($definition) ? $definition['class'] : $definition;
                $module->modelMap[$name] = $modelClass;
                if (in_array($name, ['User', 'Profile', 'Token', 'Account', 'Action', 'Log'])) {
                    Yii::$container->set("{$name}Query", fn() => $modelClass::find());
                }
            }
            if ($app instanceof \yii\console\Application) {
                $app->controllerMap['user'] = [
                    'class' => 'chipmob\user\commands\UserController',
                    'defaultAction' => 'create',
                ];
                $app->controllerMap['migrate']['migrationPath'][] = '@user/migrations';
                $module->defaultRoute = 'user';
            } else {
                $webUserClass = Yii::$app->has('user', false) ? Yii::$app->getComponents()['user']['class'] : 'yii\web\User';
                Yii::$container->set($webUserClass, [
                    'identityClass' => $module->modelMap['User'],
                    'loginUrl' => ['/user/security/login'],
                    'on afterLogin' => fn($event) => Action::saveLogin($event),
                    'on afterLogout' => fn($event) => Action::saveLogout($event),
                ]);
                $configUrlRule = [
                    'class' => 'yii\web\GroupUrlRule',
                    'prefix' => $module->urlPrefix,
                    'rules' => $module->urlRules,
                ];
                if ($module->urlPrefix != 'user') {
                    $configUrlRule['routePrefix'] = 'user';
                }
                $rule = Yii::createObject($configUrlRule);
                $app->urlManager->addRules([$rule], false);
            }
            $app->i18n->translations['user*'] = [
                'class' => 'yii\i18n\PhpMessageSource',
                'basePath' => '@user/messages',
                'sourceLanguage' => 'en-US',
            ];
            Yii::$container->set(Mailer::class, $module->mailerConfig);
        }
    }
}
