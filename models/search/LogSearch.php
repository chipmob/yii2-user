<?php

namespace chipmob\user\models\search;

use chipmob\user\components\traits\ModuleTrait;
use chipmob\user\components\traits\UserTrait;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;

class LogSearch extends Model
{
    use ModuleTrait;
    use UserTrait;

    public ?string $created_by = null;

    /** @inheritdoc */
    public function rules()
    {
        return [
            [['created_by'], 'integer'],
        ];
    }

    public function search(array $params): ActiveDataProvider
    {
        /** @var ActiveQuery $query */
        $query = Yii::$container->get('LogQuery');

        $query->joinWith(['author'])->where(['user_id' => $this->user->id]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['created_at' => SORT_DESC]],
        ]);

        if (!$this->load($params) || !$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere(['created_by' => $this->created_by]);

        return $dataProvider;
    }
}
