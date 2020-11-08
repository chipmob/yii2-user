<?php

namespace chipmob\user\components\clients;

use Yii;
use yii\helpers\ArrayHelper;

class Yandex extends \yii\authclient\clients\Yandex implements ClientInterface
{
    public function getEmail(): ?string
    {
        $emails = ArrayHelper::getValue($this->getUserAttributes(), 'emails');

        if ($emails !== null && isset($emails[0])) {
            return $emails[0];
        } else {
            return null;
        }
    }

    public function getUsername(): ?string
    {
        return ArrayHelper::getValue($this->getUserAttributes(), 'login');
    }

    /** @inheritdoc */
    protected function defaultTitle()
    {
        return Yii::t('user', 'Yandex');
    }
}
