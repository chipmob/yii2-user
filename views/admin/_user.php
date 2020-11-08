<?php

/**
 * @var yii\bootstrap4\ActiveForm $form
 * @var chipmob\user\models\User $user
 */

?>
<?= $form->field($user, 'email')->input('email', ['disabled' => !$user->isNewRecord]) ?>
<?= $form->field($user, 'username')->textInput(['autocapitalize' => 'none', 'autocomplete' => 'off', 'autocorrect' => 'off']) ?>
<?= $form->field($user, 'password')->passwordInput() ?>
