<?php

namespace chipmob\user\components\actions;

use chipmob\user\components\traits\ModuleTrait;
use chipmob\user\models\form\LoginForm;
use chipmob\user\models\User;
use Yii;
use yii\base\Action;
use yii\helpers\Url;
use yii\web\Response;

/**
 * Displays the login page.
 */
class LoginAction extends Action
{
    use ModuleTrait;

    /**
     * @return string|Response
     */
    public function run()
    {
        $model = Yii::createObject(LoginForm::class);

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->user->isTotp) {
                Yii::$app->session->set(User::TOTP_SESSION_KEY, $model->user->id);
                return $this->controller->redirect(Url::to(['/user/security/totp', 'rememberMe' => $model->rememberMe]));
            } else {
                Yii::$app->user->login($model->user, $model->rememberMe ? $this->module->rememberFor : $this->module->loginLifetime);
                return $this->controller->goBack();
            }
        }

        return $this->controller->render('login', [
            'model' => $model,
        ]);
    }
}
