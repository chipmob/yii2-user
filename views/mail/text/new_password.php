<?php

use yii\helpers\ArrayHelper;

/**
 * @var array $params
 */

?>
<?= Yii::t('user', 'Hello') ?>,
<?= Yii::t('user', 'Your account on {0} has a new password', Yii::$app->name) ?>.
<?= Yii::t('user', 'We have generated a password for you') ?>:
<?= ArrayHelper::getValue($params, 'password') ?>
<?= Yii::t('user', 'If you did not make this request you can ignore this email') ?>.
