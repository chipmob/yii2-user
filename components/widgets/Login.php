<?php

namespace chipmob\user\components\widgets;

use yii\bootstrap4\Widget;

/**
 * Login for widget.
 */
class Login extends Widget
{
    /** @inheritdoc */
    public function run()
    {
        return $this->render('login');
    }
}
