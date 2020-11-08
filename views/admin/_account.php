<?php

use yii\bootstrap4\ActiveForm;
use yii\bootstrap4\Html;

/**
 * @var yii\web\View $this
 * @var chipmob\user\models\User $user
 */

?>
<?php $this->beginContent('@user/views/admin/update.php', ['user' => $user]) ?>

<?php $form = ActiveForm::begin([
    'layout' => 'horizontal',
    'enableAjaxValidation' => true,
    'enableClientValidation' => false,
    'fieldConfig' => [
        'horizontalCssClasses' => ['wrapper' => 'col-sm-9'],
    ],
]); ?>
<?= $this->render('_user', ['form' => $form, 'user' => $user]) ?>
<div class="form-group row">
    <div class="col-sm-9 offset-sm-2">
        <?= Html::submitButton(Yii::t('user', 'Update'), ['class' => 'btn btn-block btn-success']) ?>
    </div>
</div>
<?php ActiveForm::end(); ?>

<?php $this->endContent() ?>
