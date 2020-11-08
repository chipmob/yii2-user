<?php

use yii\bootstrap4\ActiveForm;
use yii\bootstrap4\Html;

/**
 * @var yii\web\View $this
 * @var chipmob\user\models\form\RecoveryForm $model
 */

$this->title = Yii::t('user', 'Reset your password');
$this->params['breadcrumbs'][] = $this->title;

$this->beginContent('@user/views/_layout.php');

$form = ActiveForm::begin([
    'id' => 'password-recovery-form',
    'enableAjaxValidation' => true,
    'enableClientValidation' => false,
]);
echo $form->field($model, 'password')->passwordInput(['autofocus' => true]);
echo Html::submitButton(Yii::t('user', 'Finish'), ['class' => 'btn btn-success btn-block']);
ActiveForm::end();

echo Html::tag('br');

$this->endContent();
