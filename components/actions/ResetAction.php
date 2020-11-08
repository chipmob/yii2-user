<?php

namespace chipmob\user\components\actions;

use chipmob\user\components\traits\AjaxValidationTrait;
use chipmob\user\components\traits\ModuleTrait;
use chipmob\user\models\form\RecoveryForm;
use chipmob\user\models\Token;
use Yii;
use yii\base\Action;
use yii\web\NotFoundHttpException;

/**
 * Displays page where user can reset password.
 */
class ResetAction extends Action
{
    use ModuleTrait;
    use AjaxValidationTrait;

    /**
     * @param int $id
     * @param string $code
     *
     * @return string
     */
    public function run(int $id, string $code)
    {
        $token = Yii::$container->get('TokenQuery')->typeRecovery($id, $code)->one();
        if (!($token instanceof Token)) {
            Yii::$app->session->setFlash('danger', Yii::t('user', 'Recovery link is invalid or expired. Please try requesting a new one.'));
            return $this->controller->render('/message', ['title' => Yii::t('user', 'Invalid or expired link')]);
        }

        $model = Yii::createObject([
            'class' => RecoveryForm::class,
            'scenario' => RecoveryForm::SCENARIO_RESET,
            'user' => $token->user,
        ]);

        $this->performAjaxValidation($model);

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $token->delete();
            $model->reset();
            return $this->controller->render('/message', ['title' => Yii::t('user', 'Password has been changed')]);
        }

        return $this->controller->render('reset', [
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
