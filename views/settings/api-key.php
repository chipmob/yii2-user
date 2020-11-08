<?php

use chipmob\user\components\widgets\InputCopyWidget;
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
    'id' => 'api-key-form',
    'options' => ['class' => 'form-horizontal'],
    'enableAjaxValidation' => false,
    'enableClientValidation' => false,
]);
echo $form->field($model, 'access_token')->widget(InputCopyWidget::class, ['options' => ['readonly' => true]]);
echo Html::submitButton(Yii::t('user', 'Renew'), ['class' => 'btn btn-block btn-warning', 'data-confirm' => Yii::t('user', 'Are you sure?')]);
ActiveForm::end();

$this->endContent();
