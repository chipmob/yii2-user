<?php

namespace chipmob\user\components\clients;

use yii\helpers\ArrayHelper;

class Google extends \yii\authclient\clients\Google implements ClientInterface
{
    public function getEmail(): ?string
    {
        return ArrayHelper::getValue($this->getUserAttributes(), 'email');
    }

    public function getUsername(): ?string
    {
        return null;
    }
}
