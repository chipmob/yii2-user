<?php

namespace chipmob\user\components\traits;

use chipmob\user\models\User;

/**
 * @property User $user
 */
trait UserTrait
{
    private User $_user;

    public function getUser(): User
    {
        return $this->_user;
    }

    public function setUser(User $user)
    {
        $this->_user = $user;
    }
}
