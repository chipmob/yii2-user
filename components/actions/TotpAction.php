<?php

namespace chipmob\user\components\actions;

use chipmob\user\components\traits\ModuleTrait;
use chipmob\user\models\form\TotpForm;
use chipmob\user\models\User;
use Yii;
use yii\base\Action;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Displays the two factor page.
 */
class TotpAction extends Action
{
    use ModuleTrait;

    private User $_user;

    /**
     * @return string|Response
     */
    public function run()
    {
        $model = Yii::createObject([
            'class' => TotpForm::class,
            'user' => $this->_user,
        ]);

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            Yii::$app->session->remove(User::TOTP_SESSION_KEY);
            Yii::$app->user->login($model->user, (bool)Yii::$app->request->get('rememberMe', false) ? $this->module->rememberFor : $this->module->loginLifetime);
            return $this->controller->goBack();
        }

        return $this->controller->render('totp', [
            'model' => $model,
        ]);
    }

    /** @inheritdoc */
    public function beforeRun()
    {
        $id = Yii::$app->session->get(User::TOTP_SESSION_KEY);
        if (empty($id)) {
            throw new NotFoundHttpException();
        }

        $user = Yii::$container->get('UserQuery')->byId((int)$id)->one();
        if (!($user instanceof User)) {
            throw new NotFoundHttpException();
        }

        $this->_user = $user;

        return parent::beforeRun();
    }
}
