<?php

namespace chipmob\user\models\query;

use chipmob\user\models\Account;
use yii\authclient\ClientInterface;
use yii\db\ActiveQuery;

/**
 * @method Account|null one($db = null)
 * @method Account[]    all($db = null)
 * @see [[Account]]
 */
class AccountQuery extends ActiveQuery
{
    public function byClient(ClientInterface $client): ActiveQuery
    {
        return $this->andWhere([
            'provider' => $client->getId(),
            'client_id' => $client->getUserAttributes()['id'],
        ]);
    }

    public function byId(int $id): ActiveQuery
    {
        return $this->andWhere(['id' => $id]);
    }
}
