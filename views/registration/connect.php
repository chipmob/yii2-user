<?php

use yii\bootstrap4\ActiveForm;
use yii\bootstrap4\Alert;
use yii\bootstrap4\Html;

/**
 * @var yii\web\View $this
 * @var chipmob\user\models\User $model
 */

$this->title = Yii::t('user', 'Sign up');
$this->params['breadcrumbs'][] = $this->title;

$this->beginContent('@user/views/_layout.php');

echo Alert::widget([
    'body' => Yii::t('user', 'In order to finish your registration, we need you to enter following fields'),
    'options' => ['class' => 'alert alert-info'],
]);

$form = ActiveForm::begin([
    'id' => 'connect-form',
    'enableAjaxValidation' => true,
    'enableClientValidation' => false,
]);
echo $form->field($model, 'email')->input('email', ['disabled' => $model->email]);
echo $form->field($model, 'username')->textInput(['autocapitalize' => 'none', 'autocomplete' => 'off', 'autocorrect' => 'off']);
echo Html::submitButton(Yii::t('user', 'Continue'), ['class' => 'btn btn-success btn-block']);
ActiveForm::end();

echo Html::tag('p', Html::a(Yii::t('user', 'If you already registered, sign in and connect this account on settings page'), ['/user/settings/networks']), ['class' => 'text-center mt-3']);

$this->endContent();
