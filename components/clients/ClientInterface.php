<?php

namespace chipmob\user\components\clients;

/**
 * Enhances default yii client interface by adding methods that can be used to
 * get user's email and username.
 *
 * @property string $email
 * @property string $username
 */
interface ClientInterface extends \yii\authclient\ClientInterface
{
    public function getEmail(): ?string;

    public function getUsername(): ?string;
}
