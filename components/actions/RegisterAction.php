<?php

namespace chipmob\user\components\actions;

use chipmob\user\components\traits\AjaxValidationTrait;
use chipmob\user\components\traits\ModuleTrait;
use chipmob\user\models\form\RegistrationForm;
use Yii;
use yii\base\Action;
use yii\web\NotFoundHttpException;

/**
 * Displays the registration page.
 * After successful registration if enableConfirmation is enabled shows info message otherwise
 * redirects to home page.
 */
class RegisterAction extends Action
{
    use ModuleTrait;
    use AjaxValidationTrait;

    /**
     * @return string
     */
    public function run()
    {
        $model = Yii::createObject(RegistrationForm::class);

        $this->performAjaxValidation($model);

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->register()) {
                Yii::$app->session->setFlash('info', Yii::t('user', 'Your account has been created and a message with further instructions has been sent to your email'));
                return $this->controller->render('/message', ['title' => Yii::t('user', 'Your account has been created')]);
            } else {
                Yii::$app->session->setFlash('danger', Yii::t('user', 'Something went wrong'));
                return $this->controller->render('/message', ['title' => Yii::t('user', 'Something went wrong')]);
            }
        }

        return $this->controller->render('register', [
            'model' => $model,
            'module' => $this->module,
        ]);
    }

    /** @inheritdoc */
    public function beforeRun()
    {
        if (!$this->module->enableRegistration) {
            throw new NotFoundHttpException();
        }

        return parent::beforeRun();
    }
}
