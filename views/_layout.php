<?php

use yii\bootstrap4\Html;

/**
 * @var yii\web\View $this
 * @var string $content
 */

?>
<div class="row justify-content-center align-items-center">
    <div class="col-lg-6 col-md-8 col-sm-12">
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