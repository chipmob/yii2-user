<?php

use chipmob\user\components\widgets\UserMenu;
use yii\bootstrap4\Html;

/**
 * @var yii\web\View $this
 * @var string $content
 */

?>
<div class="row">
    <div class="col-md-3">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><?= Yii::$app->user->identity->username ?></h3>
            </div>
            <div class="card-body">
                <?= UserMenu::widget() ?>
            </div>
        </div>
    </div>
    <div class="col-md-9">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><?= Html::encode($this->title) ?></h3>
            </div>
            <div class="card-body">
                <?= $content ?>
            </div>
        </div>
    </div>
</div>
