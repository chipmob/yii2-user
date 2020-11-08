<?php

namespace chipmob\user\models\query;

use chipmob\user\models\Token;
use yii\db\ActiveQuery;

/**
 * @method Token|null one($db = null)
 * @method Token[]    all($db = null)
 * @see [[Token]]
 */
class TokenQuery extends ActiveQuery
{
    /** @inheritdoc */
    public function init()
    {
        parent::init();

        $this->andWhere(['>', 'expired_at', time()]);
    }

    public function typeConfirmation(int $id, string $code): ActiveQuery
    {
        return $this->andWhere(['user_id' => $id, 'code' => $code, 'type' => Token::TYPE_CONFIRMATION]);
    }

    public function typeRecovery(int $id, string $code): ActiveQuery
    {
        return $this->andWhere(['user_id' => $id, 'code' => $code, 'type' => Token::TYPE_RECOVERY]);
    }
}
