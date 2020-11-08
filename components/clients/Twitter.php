<?php

namespace chipmob\user\components\clients;

use yii\helpers\ArrayHelper;

class Twitter extends \yii\authclient\clients\Twitter implements ClientInterface
{
    public function getUsername(): ?string
    {
        return ArrayHelper::getValue($this->getUserAttributes(), 'screen_name');
    }

    public function getEmail(): ?string
    {
        return ArrayHelper::getValue($this->getUserAttributes(), 'email');
    }
}
