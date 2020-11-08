<?php

namespace chipmob\user\models\query;

use chipmob\user\models\User;
use yii\db\ActiveQuery;

/**
 * @method User|null one($db = null)
 * @method User[]    all($db = null)
 * @see [[User]]
 */
class UserQuery extends ActiveQuery
{
    public function active(): ActiveQuery
    {
        return $this->andWhere(['removed_at' => null]);
    }

    public function byUsernameOrEmail(string $usernameOrEmail): ActiveQuery
    {
        return filter_var($usernameOrEmail, FILTER_VALIDATE_EMAIL) ? $this->byEmail($usernameOrEmail) : $this->byUsername($usernameOrEmail);
    }

    public function byEmail(string $email): ActiveQuery
    {
        return $this->andWhere(['email' => $email]);
    }

    public function byUsername(string $username): ActiveQuery
    {
        return $this->andWhere(['username' => $username]);
    }

    public function byId(int $id): ActiveQuery
    {
        return $this->andWhere(['id' => $id]);
    }
}
