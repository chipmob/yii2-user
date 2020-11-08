<?php

use yii\bootstrap4\ActiveForm;
use yii\bootstrap4\Html;

/**
 * @var yii\web\View $this
 * @var chipmob\user\models\form\ResendForm $model
 */

$this->title = Yii::t('user', 'Request new confirmation message');
$this->params['breadcrumbs'][] = $this->title;

$this->beginContent('@user/views/_layout.php');

$form = ActiveForm::begin([
    'id' => 'resend-form',
    'enableAjaxValidation' => true,
    'enableClientValidation' => false,
]);
echo $form->field($model, 'email')->input('email', ['autofocus' => true]);
echo Html::submitButton(Yii::t('user', 'Continue'), ['class' => 'btn btn-primary btn-block']);
ActiveForm::end();

echo Html::tag('br');

$this->endContent();
