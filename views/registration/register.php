<?php

use yii\bootstrap4\ActiveForm;
use yii\bootstrap4\Html;

/**
 * @var yii\web\View $this
 * @var chipmob\user\models\form\RegistrationForm $model
 * @var chipmob\user\Module $module
 */

$this->title = Yii::t('user', 'Sign up');
$this->params['breadcrumbs'][] = $this->title;

$this->beginContent('@user/views/_layout.php');

$form = ActiveForm::begin([
    'id' => 'registration-form',
    'enableAjaxValidation' => true,
    'enableClientValidation' => false,
]);
echo $form->field($model, 'email')->input('email');
echo $form->field($model, 'username')->textInput(['autocapitalize' => 'none', 'autocomplete' => 'off', 'autocorrect' => 'off']);
if ($module->enableGeneratingPassword === false) {
    echo $form->field($model, 'password')->passwordInput();
}
echo Html::submitButton(Yii::t('user', 'Sign up'), ['class' => 'btn btn-success btn-block']);
ActiveForm::end();

echo Html::tag('p', Html::a(Yii::t('user', 'Already registered? Sign in!'), ['/user/security/login']), ['class' => 'text-center mt-3']);

$this->endContent();
