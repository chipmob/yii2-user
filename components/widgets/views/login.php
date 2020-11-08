<?php

use chipmob\user\models\form\LoginForm;
use yii\bootstrap4\ActiveForm;
use yii\bootstrap4\Html;
use yii\helpers\Url;

/**
 * @var yii\web\View $this
 */

if (Yii::$app->user->isGuest) {
    $model = Yii::createObject(LoginForm::class);
    $form = ActiveForm::begin([
        'id' => 'login-widget-form',
        'action' => Url::to(['/user/security/login']),
        'enableAjaxValidation' => true,
        'enableClientValidation' => false,
        'validateOnBlur' => false,
        'validateOnType' => false,
        'validateOnChange' => false,
    ]);
    echo $form->field($model, 'login')->textInput(['placeholder' => 'Login', 'autofocus' => true, 'autocapitalize' => 'none', 'autocomplete' => 'off', 'autocorrect' => 'off']);
    echo $form->field($model, 'password')->passwordInput(['placeholder' => 'Password']);
    echo $form->field($model, 'rememberMe')->checkbox();
    echo Html::submitButton(Yii::t('user', 'Sign in'), ['class' => 'btn btn-primary btn-block']);
    ActiveForm::end();
} else {
    echo Html::a(Yii::t('user', 'Logout'), ['/user/security/logout'], ['class' => 'btn btn-danger btn-block', 'data-method' => 'post']);
}
