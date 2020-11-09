<?php

use chipmob\user\components\widgets\Connect;
use yii\bootstrap4\ActiveForm;
use yii\bootstrap4\Html;

/**
 * @var yii\web\View $this
 * @var chipmob\user\models\form\LoginForm $model
 */

$this->title = Yii::t('user', 'Sign in');
$this->params['breadcrumbs'][] = $this->title;

$this->beginContent('@user/views/_layout.php');

$form = ActiveForm::begin([
    'id' => 'login-form',
    'enableAjaxValidation' => false,
    'enableClientValidation' => false,
    'enableClientScript' => false,
]);
echo $form->field($model, 'login')->textInput(['autofocus' => true, 'autocapitalize' => 'none', 'autocomplete' => 'off', 'autocorrect' => 'off']);
echo $form->field($model, 'password')->passwordInput();
echo $form->field($model, 'rememberMe')->checkbox();
echo Html::submitButton(Yii::t('user', 'Sign in'), ['class' => 'btn btn-primary btn-block']);
ActiveForm::end();

if ($model->module->enablePasswordRecovery) {
    echo Html::tag('p', Html::a(Yii::t('user', 'Forgot password?'), ['/user/recovery/request']), ['class' => 'text-center mt-3']);
}
if ($model->module->enableConfirmation) {
    echo Html::tag('p', Html::a(Yii::t('user', 'Didn\'t receive confirmation message?'), ['/user/registration/resend']), ['class' => 'text-center mt-3']);
}
if ($model->module->enableRegistration) {
    echo Html::tag('p', Html::a(Yii::t('user', 'Don\'t have an account? Sign up!'), ['/user/registration/register']), ['class' => 'text-center mt-3']);
}

echo Html::tag('hr');

echo Connect::widget(['baseAuthUrl' => ['/user/security/auth']]);

$this->endContent();
