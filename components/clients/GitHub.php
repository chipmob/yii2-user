<?php

namespace chipmob\user\components\clients;

use yii\helpers\ArrayHelper;

class GitHub extends \yii\authclient\clients\GitHub implements ClientInterface
{
    public function getEmail(): ?string
    {
        return ArrayHelper::getValue($this->getUserAttributes(), 'email');
    }

    public function getUsername(): ?string
    {
        return ArrayHelper::getValue($this->getUserAttributes(), 'login');
    }
}
