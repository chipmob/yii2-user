<?php

namespace chipmob\user\commands;

use chipmob\user\models\Token;
use chipmob\user\models\User;
use Yii;
use yii\console\Controller;
use yii\helpers\Console;

/**
 * Manages user's account.
 */
class UserController extends Controller
{
    /**
     * This command creates new user account. If password is not set, this command will generate new 8-char password.
     * After saving user to database, this command uses mailer component to send credentials (username and password) to
     * user via email.
     *
     * @param string $email Email address
     * @param string $username Username
     * @param null|string $password Password (if null it will be generated automatically)
     */
    public function actionCreate(string $email, string $username, ?string $password = null)
    {
        $user = Yii::createObject([
            'class' => User::class,
            'scenario' => User::SCENARIO_CREATE,
            'email' => $email,
            'username' => $username,
            'password' => $password,
        ]);
        if ($user->create()) {
            $this->stdout(Yii::t('user', 'User has been created') . PHP_EOL, Console::FG_GREEN);
        } else {
            $this->stdout(Yii::t('user', 'Please fix following errors:') . PHP_EOL, Console::FG_RED);
            foreach ($user->errors as $errors) {
                foreach ($errors as $error) {
                    $this->stdout(' - ' . $error . "\n", Console::FG_RED);
                }
            }
        }
    }

    /**
     * Confirms a user by setting confirmed_at field to current time.
     *
     * @param string $search Email or username
     */
    public function actionConfirm(string $search)
    {
        $user = Yii::$container->get('UserQuery')->byUsernameOrEmail($search)->one();
        if (!($user instanceof User)) {
            $this->stdout(Yii::t('user', 'User is not found') . PHP_EOL, Console::FG_RED);
        } else {
            if ($user->confirm()) {
                $this->stdout(Yii::t('user', 'User has been confirmed') . PHP_EOL, Console::FG_GREEN);
            } else {
                $this->stdout(Yii::t('user', 'Error occurred while confirming user') . PHP_EOL, Console::FG_RED);
            }
        }
    }

    /**
     * Deletes a user.
     *
     * @param string $search Email or username
     */
    public function actionDelete(string $search)
    {
        if ($this->confirm(Yii::t('user', 'Are you sure? Deleted user can not be restored'))) {
            $user = Yii::$container->get('UserQuery')->byUsernameOrEmail($search)->one();
            if (!($user instanceof User)) {
                $this->stdout(Yii::t('user', 'User is not found') . PHP_EOL, Console::FG_RED);
            } else {
                if ($user->delete()) {
                    $this->stdout(Yii::t('user', 'User has been deleted') . PHP_EOL, Console::FG_GREEN);
                } else {
                    $this->stdout(Yii::t('user', 'Error occurred while deleting user') . PHP_EOL, Console::FG_RED);
                }
            }
        }
    }

    /**
     * Updates user's password to given.
     *
     * @param string $search Email or username
     * @param string $password New password
     */
    public function actionPassword(string $search, string $password)
    {
        $user = Yii::$container->get('UserQuery')->byUsernameOrEmail($search)->one();
        if (!($user instanceof User)) {
            $this->stdout(Yii::t('user', 'User is not found') . PHP_EOL, Console::FG_RED);
        } else {
            $user->setScenario(User::SCENARIO_SETTINGS);
            $user->password = $password;
            if ($user->validate('password') && $user->resetPassword()) {
                $this->stdout(Yii::t('user', 'Password has been changed') . PHP_EOL, Console::FG_GREEN);
            } else {
                $this->stdout(Yii::t('user', 'Error occurred while changing password') . PHP_EOL, Console::FG_RED);
            }
        }
    }
}
