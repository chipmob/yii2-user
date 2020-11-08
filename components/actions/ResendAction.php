<?php

namespace chipmob\user\components\actions;

use chipmob\user\components\traits\AjaxValidationTrait;
use chipmob\user\components\traits\ModuleTrait;
use chipmob\user\models\form\ResendForm;
use Yii;
use yii\base\Action;
use yii\web\NotFoundHttpException;

/**
 * Displays page where user can request new confirmation token. If resending was successful, displays message.
 */
class ResendAction extends Action
{
    use ModuleTrait;
    use AjaxValidationTrait;

    /**
     * @return string
     */
    public function run()
    {
        $model = Yii::createObject(ResendForm::class);

        $this->performAjaxValidation($model);

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $model->resend();
            Yii::$app->session->setFlash('info', Yii::t('user', 'A message has been sent to your email address. It contains a confirmation link that you must click to complete registration.'));
            return $this->controller->render('/message', ['title' => Yii::t('user', 'A new confirmation link has been sent')]);
        }

        return $this->controller->render('resend', [
            'model' => $model,
        ]);
    }

    /** @inheritdoc */
    public function beforeRun()
    {
        if ($this->module->enableConfirmation == false) {
            throw new NotFoundHttpException();
        }

        return parent::beforeRun();
    }
}
