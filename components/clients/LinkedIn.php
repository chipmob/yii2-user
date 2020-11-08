<?php

namespace chipmob\user\components\clients;

use yii\helpers\ArrayHelper;

class LinkedIn extends \yii\authclient\clients\LinkedIn implements ClientInterface
{
    public function getEmail(): ?string
    {
        return ArrayHelper::getValue($this->getUserAttributes(), 'email-address');
    }

    public function getUsername(): ?string
    {
        return null;
    }
}
