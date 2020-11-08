<?php

use chipmob\user\components\widgets\InputCopyWidget;
use yii\bootstrap4\ActiveForm;
use yii\bootstrap4\Html;

/**
 * @var $this yii\web\View
 * @var $model chipmob\user\models\form\GoogleTotpForm
 */

$this->title = Yii::t('user', 'Google two-step authentication');
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

$this->beginContent('@user/views/settings/_layout.php');

$form = ActiveForm::begin([
    'id' => 'google-totp-form',
    'options' => ['class' => 'form-horizontal'],
    'enableAjaxValidation' => false,
    'enableClientValidation' => false,
    'validateOnBlur' => false,
]);
echo Html::img($model->imageSrc, ['class' => 'img-fluid mb-3']);
echo $form->field($model, 'totp_key')->widget(InputCopyWidget::class, ['options' => ['readonly' => true]]);
if ($model->user->isTotp) {
    echo Html::submitButton(Yii::t('user', 'Turn off'), ['class' => 'btn btn-block btn-danger', 'data-confirm' => Yii::t('user', 'Are you sure you want to turn off?')]);
} else {
    echo $form->field($model, 'totp_code')->input('number', ['autofocus' => true, 'autocomplete' => 'off']);
    echo Html::submitButton(Yii::t('user', 'Save'), ['class' => 'btn btn-block btn-success']);
}
ActiveForm::end();

$this->endContent();
