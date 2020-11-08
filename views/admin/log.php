<?php

use chipmob\user\models\Log;
use yii\grid\GridView;
use yii\helpers\Html;

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var chipmob\user\models\search\LogSearch $searchModel
 */

$this->title = Yii::t('user', 'Log list for {0}', [$searchModel->user->username]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('user', 'Users'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'columns' => [
        [
            'attribute' => 'created_at',
            'value' => fn(Log $model) => Yii::$app->formatter->asDatetime($model->created_at),
            'filter' => false,
        ],
        [
            'attribute' => 'ip',
            'filter' => false,
            'enableSorting' => false,
        ],
        [
            'attribute' => 'ua',
            'filter' => false,
            'enableSorting' => false,
        ],
        [
            'attribute' => 'data',
            'value' => fn(Log $model) => Html::tag('code', $model->data), // TODO: add pretty formatter
            'format' => 'raw',
            'filter' => false,
            'enableSorting' => false,
        ],
        [
            'attribute' => 'created_by',
            'value' => fn(Log $model) => $model->author->username, // TODO: if `$model->author` is null, get `->username` do not raise error
            'filter' => Html::activeDropDownList($searchModel, 'created_by',
                Log::getListOfAuthors(),
                [
                    'prompt' => Yii::t('user', 'Not selected'),
                    'class' => 'form-control',
                ]
            ),
            'contentOptions' => fn(Log $model) => ['class' => $model->user_id == $model->created_by ? 'text-success' : 'text-danger'],
            'enableSorting' => false,
        ],
    ],
]);
