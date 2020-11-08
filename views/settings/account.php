<?php

use yii\bootstrap4\ActiveForm;
use yii\bootstrap4\Html;

/**
 * @var yii\web\View $this
 * @var chipmob\user\models\form\SettingsForm $model
 */

$this->title = Yii::t('user', 'Account settings');
$this->params['breadcrumbs'][] = $this->title;

$this->beginContent('@user/views/settings/_layout.php');

$form = ActiveForm::begin([
    'id' => 'account-form',
    'options' => ['class' => 'form-horizontal'],
    'enableAjaxValidation' => true,
    'enableClientValidation' => false,
]);
echo $form->field($model, 'email')->textInput(['disabled' => !$model->user->isNewRecord]);
echo $form->field($model, 'username')->textInput(['autocapitalize' => 'none', 'autocomplete' => 'off', 'autocorrect' => 'off']);
echo $form->field($model, 'password')->passwordInput();
echo Html::tag('hr');
echo $form->field($model, 'current_password')->passwordInput();
echo Html::submitButton(Yii::t('user', 'Save'), ['class' => 'btn btn-block btn-success']);
ActiveForm::end();

if ($model->module->enableAccountDelete) {
    echo Html::tag('hr');
    echo Html::a(Yii::t('user', 'Delete account'), ['delete'], [
        'class' => 'btn btn-block btn-danger',
        'data-method' => 'post',
        'data-confirm' => Yii::t('user', 'Are you sure? There is no going back'),
    ]);
}

$this->endContent();
