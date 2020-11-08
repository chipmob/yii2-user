<?php

use yii\helpers\ArrayHelper;

/**
 * @var chipmob\user\models\User $user
 * @var array $params
 */

?>
<?= Yii::t('user', 'Hello') ?>,
<?= Yii::t('user', 'Your account on {0} has been created', Yii::$app->name) ?>.
<?php if (Yii::$app->getModule('user')->enableGeneratingPassword || in_array($user->scenario, [$user::SCENARIO_CREATE, $user::SCENARIO_CONNECT])): ?>
<?= Yii::t('user', 'We have generated a password for you') ?>:
<?= ArrayHelper::getValue($params, 'password') ?>
<?php endif ?>
<?php if ($url = ArrayHelper::getValue($params, 'token_url')): ?>
<?= Yii::t('user', 'In order to complete your registration, please click the link below') ?>.
<?= $url ?>
<?= Yii::t('user', 'If you cannot click the link, please try pasting the text into your browser') ?>.
<?php endif ?>
<?= Yii::t('user', 'If you did not make this request you can ignore this email') ?>.
