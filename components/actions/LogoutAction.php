<?php

namespace chipmob\user\components\actions;

use Yii;
use yii\base\Action;
use yii\web\Response;

/**
 * Logs the user out and then redirects to the homepage.
 */
class LogoutAction extends Action
{
    /**
     * @return Response
     */
    public function run()
    {
        Yii::$app->user->logout();
        return $this->controller->goHome();
    }
}
