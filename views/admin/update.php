<?php

use chipmob\user\components\widgets\AdminMenu;

/**
 * @var yii\web\View $this
 * @var chipmob\user\models\User $user
 * @var string $content
 */

$this->title = Yii::t('user', 'Update user account');
$this->params['breadcrumbs'][] = ['label' => Yii::t('user', 'Users'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<?= $this->render('@user/views/admin/_menu') ?>
<div class="row">
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <?= AdminMenu::widget(['user' => $user]) ?>
            </div>
        </div>
    </div>
    <div class="col-md-9">
        <div class="card">
            <div class="card-body">
                <?= $content ?>
            </div>
        </div>
    </div>
</div>
