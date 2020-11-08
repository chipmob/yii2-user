<?php

namespace chipmob\user\models\search;

use chipmob\user\components\traits\ModuleTrait;
use chipmob\user\models\query\UserQuery;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * UserSearch represents the model behind the search form about User.
 */
class UserSearch extends Model
{
    use ModuleTrait;

    public ?string $id = null;
    public ?string $username = null;
    public ?string $email = null;
    public ?string $is_confirmed = null;
    public ?string $is_blocked = null;
    public ?string $is_removed = null;

    /** @inheritdoc */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['username', 'email'], 'string'],
            [['is_confirmed', 'is_blocked', 'is_removed'], 'integer', 'min' => 0, 'max' => 1],
        ];
    }

    /** @inheritdoc */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('user', 'ID'),
            'username' => Yii::t('user', 'Username'),
            'email' => Yii::t('user', 'Email'),
            'created_at' => Yii::t('user', 'Created at'),
        ];
    }

    public function search(array $params): ActiveDataProvider
    {
        /** @var UserQuery $query */
        $query = Yii::$container->get('UserQuery');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['id' => SORT_DESC]],
        ]);

        if (!$this->load($params) || !$this->validate()) {
            return $dataProvider;
        }

        switch ($this->is_confirmed) {
            case '1':
                $query->andWhere(['is not', 'confirmed_at', null]);
                break;
            case '0':
                $query->andWhere(['is', 'confirmed_at', null]);
                break;
        }

        switch ($this->is_blocked) {
            case '1':
                $query->andWhere(['is not', 'blocked_at', null]);
                break;
            case '0':
                $query->andWhere(['is', 'blocked_at', null]);
                break;
        }

        switch ($this->is_removed) {
            case '1':
                $query->andWhere(['is not', 'removed_at', null]);
                break;
            case '0':
                $query->andWhere(['is', 'removed_at', null]);
                break;
        }

        $query->andFilterWhere(['id' => $this->id])
            ->andFilterWhere(['like', 'username', $this->username])
            ->andFilterWhere(['like', 'email', $this->email]);

        return $dataProvider;
    }
}
