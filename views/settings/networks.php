<?php

use chipmob\user\components\widgets\Connect;
use yii\bootstrap4\Alert;

/**
 * @var yii\web\View $this
 */

$this->title = Yii::t('user', 'Networks');
$this->params['breadcrumbs'][] = $this->title;

$this->beginContent('@user/views/settings/_layout.php');

echo Alert::widget([
    'body' => Yii::t('user', 'You can connect multiple accounts to be able to log in using them'),
    'options' => ['class' => 'alert alert-info'],
]);

echo Connect::widget([
    'baseAuthUrl' => ['/user/security/auth'],
    'accounts' => Yii::$app->user->identity->accounts,
    'popupMode' => false,
]);

$this->endContent();
