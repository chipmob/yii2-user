<?php

use chipmob\user\models\Action;
use yii\bootstrap4\Html;
use yii\grid\GridView;
use yii\helpers\ArrayHelper;

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var chipmob\user\models\search\ActionSearch $searchModel
 */

$this->title = Yii::t('user', 'Action list for {0}', [$searchModel->user->username]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('user', 'Users'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'columns' => [
        [
            'attribute' => 'created_at',
            'value' => fn(Action $model) => Yii::$app->formatter->asDatetime($model->created_at),
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
            'attribute' => 'type',
            'value' => fn(Action $model) => ArrayHelper::getValue(Action::getListOfActions(), $model->type),
            'filter' => Html::activeDropDownList($searchModel, 'type',
                Action::getListOfActions(),
                [
                    'prompt' => Yii::t('user', 'Not selected'),
                    'class' => 'form-control',
                ]
            ),
            'format' => 'html',
            'enableSorting' => false,
            'visible' => $searchModel->module->enableConfirmation,
        ],
    ],
]);
