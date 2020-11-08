<?php

use yii\bootstrap4\ActiveForm;
use yii\bootstrap4\Html;

/**
 * @var yii\web\View $this
 * @var chipmob\user\models\form\LoginForm $model
 */

$this->title = Yii::t('user', 'Two-step security');
$this->params['breadcrumbs'][] = $this->title;

$css = <<<CSS
input[type=number]::-webkit-outer-spin-button,
input[type=number]::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}
input[type=number] {
    -moz-appearance:textfield;
}
CSS;
$this->registerCss($css);

$this->beginContent('@user/views/_layout.php');

$form = ActiveForm::begin([
    'id' => 'totp-form',
    'enableAjaxValidation' => false,
    'enableClientValidation' => false,
    'enableClientScript' => false,
]);
echo $form->field($model, 'totp_code')->input('number', ['autofocus' => true, 'autocomplete' => 'off']);
echo Html::submitButton(Yii::t('user', 'Sign in'), ['class' => 'btn btn-primary btn-block']);
ActiveForm::end();

$this->endContent();
