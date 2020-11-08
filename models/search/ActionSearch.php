<?php

namespace chipmob\user\models\search;

use chipmob\user\components\traits\ModuleTrait;
use chipmob\user\components\traits\UserTrait;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;

class ActionSearch extends Model
{
    use ModuleTrait;
    use UserTrait;

    public ?string $type = null;

    /** @inheritdoc */
    public function rules()
    {
        return [
            [['type'], 'integer'],
        ];
    }

    public function search(array $params): ActiveDataProvider
    {
        /** @var ActiveQuery $query */
        $query = Yii::$container->get('ActionQuery');

        $query->innerJoinWith('user')->where(['user_id' => $this->user->id]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['created_at' => SORT_DESC]],
        ]);

        if (!$this->load($params) || !$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere(['type' => $this->type]);

        return $dataProvider;
    }
}
