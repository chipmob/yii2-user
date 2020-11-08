<?php

namespace chipmob\user\components\actions;

use chipmob\user\components\traits\AjaxValidationTrait;
use chipmob\user\components\traits\ModuleTrait;
use chipmob\user\models\form\RecoveryForm;
use Yii;
use yii\base\Action;
use yii\web\NotFoundHttpException;

/**
 * Shows page where user can request password recovery.
 */
class RequestAction extends Action
{
    use ModuleTrait;
    use AjaxValidationTrait;

    /**
     * @return string
     */
    public function run()
    {
        $model = Yii::createObject([
            'class' => RecoveryForm::class,
            'scenario' => RecoveryForm::SCENARIO_REQUEST,
        ]);

        $this->performAjaxValidation($model);

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $model->recover();
            Yii::$app->session->setFlash('info', Yii::t('user', 'An email has been sent with instructions for resetting your password'));
            return $this->controller->render('/message', ['title' => Yii::t('user', 'Recovery message sent')]);
        }

        return $this->controller->render('request', [
            'model' => $model,
        ]);
    }

    /** @inheritdoc */
    public function beforeRun()
    {
        if (!$this->module->enablePasswordRecovery) {
            throw new NotFoundHttpException();
        }

        return parent::beforeRun();
    }
}
