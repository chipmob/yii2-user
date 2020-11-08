<?php

namespace chipmob\user\components\widgets;

use chipmob\user\components\assets\ClipboardAsset;
use Yii;
use yii\bootstrap4\InputWidget;
use yii\helpers\Html;

class InputCopyWidget extends InputWidget
{
    public array $inputOptions = [];

    public string $inputTemplate = <<<HTML
<div class="input-group" data-clipboard-form-input>
    <div class="input-group-prepend">
        <div class="input-group-text" role="button">
            <i class="fas fa-clipboard"></i>
        </div>
    </div>
    {input}
</div>
HTML;

    public function init()
    {
        $view = Yii::$app->view;
        ClipboardAsset::register($view);

        $js = <<<JS
new ClipboardJS('[data-clipboard-form-input]', {
    target: function(trigger) {
        return trigger.querySelector('input');
    }
})
JS;
        $view->registerJs($js);
    }

    /** @inheritdoc */
    public function run()
    {
        return str_replace('{input}', Html::activeTextInput($this->model, $this->attribute, $this->options), $this->inputTemplate);
    }
}
