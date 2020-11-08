<?php

namespace chipmob\user\components\actions;

use chipmob\user\components\traits\AjaxValidationTrait;
use chipmob\user\components\traits\ModuleTrait;
use chipmob\user\models\Account;
use chipmob\user\models\User;
use Yii;
use yii\base\Action;
use yii\web\NotFoundHttpException;

/**
 * Displays page where user can create new account that will be connected to social account.
 */
class ConnectAction extends Action
{
    use ModuleTrait;
    use AjaxValidationTrait;

    private Account $_account;

    /**
     * @return string
     */
    public function run()
    {
        $user = Yii::createObject([
            'class' => User::class,
            'scenario' => User::SCENARIO_CONNECT,
            'username' => $this->_account->username,
            'email' => $this->_account->email,
        ]);

        $this->performAjaxValidation($user);

        if ($user->load(Yii::$app->request->post()) && $user->create()) {
            Yii::$app->session->remove(Account::CONNECT_SESSION_KEY);
            if ($this->_account->process($user)) {
                Yii::$app->session->set(User::OAUTH_SESSION_KEY, $user->id);
                Yii::$app->user->login($user, $this->module->loginLifetime);
            }
            Yii::$app->session->setFlash('info', Yii::t('user', 'Your account has been created and a message with further instructions has been sent to your email'));
            return $this->controller->goBack();
        }

        return $this->controller->render('connect', [
            'model' => $user,
        ]);
    }

    /** @inheritdoc */
    public function beforeRun()
    {
        $id = Yii::$app->session->get(Account::CONNECT_SESSION_KEY);
        if (empty($id)) {
            throw new NotFoundHttpException();
        }

        $account = Yii::$container->get('AccountQuery')->byId((int)$id)->one();
        if (!($account instanceof Account) || $account->isConnected) {
            throw new NotFoundHttpException();
        }

        $this->_account = $account;

        return parent::beforeRun();
    }
}
