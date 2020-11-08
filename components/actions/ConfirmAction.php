<?php

namespace chipmob\user\components\actions;

use chipmob\user\components\traits\ModuleTrait;
use chipmob\user\models\Token;
use chipmob\user\models\User;
use Yii;
use yii\base\Action;
use yii\web\NotFoundHttpException;

/**
 * Confirms user's account. If confirmation was successful logs the user and shows success message. Otherwise
 * shows error message.
 */
class ConfirmAction extends Action
{
    use ModuleTrait;

    /**
     * @param int $id
     * @param string $code
     *
     * @return string
     * @throws NotFoundHttpException
     */
    public function run(int $id, string $code)
    {
        $user = Yii::$container->get('UserQuery')->active()->byId($id)->one();
        if (!($user instanceof User)) {
            throw new NotFoundHttpException();
        }

        $token = Yii::$container->get('TokenQuery')->typeConfirmation($user->id, $code)->one();
        if (!($token instanceof Token)) {
            Yii::$app->session->setFlash('danger', Yii::t('user', 'The confirmation link is invalid or expired. Please try requesting a new one.'));
        } else {
            if ($user->confirm()) {
                $token->delete();
                Yii::$app->session->setFlash('success', Yii::t('user', 'Thank you, registration is now complete'));
                Yii::$app->user->login($user, $this->module->loginLifetime);
            } else {
                Yii::$app->session->setFlash('danger', Yii::t('user', 'Something went wrong and your account has not been confirmed'));
            }
        }

        return $this->controller->render('/message', ['title' => Yii::t('user', 'Account confirmation')]);
    }

    /** @inheritdoc */
    public function beforeRun()
    {
        if (!$this->module->enableConfirmation) {
            throw new NotFoundHttpException();
        }

        return parent::beforeRun();
    }
}
