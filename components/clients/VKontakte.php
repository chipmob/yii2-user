<?php

namespace chipmob\user\components\clients;

use Yii;
use yii\helpers\ArrayHelper;

class VKontakte extends \yii\authclient\clients\VKontakte implements ClientInterface
{
    /** @inheritdoc */
    public $scope = 'email';

    public function getEmail(): ?string
    {
        return $this->getAccessToken()->getParam('email');
    }

    public function getUsername(): ?string
    {
        return ArrayHelper::getValue($this->getUserAttributes(), 'screen_name');
    }

    /** @inheritdoc */
    protected function defaultTitle()
    {
        return Yii::t('user', 'VKontakte');
    }
}
