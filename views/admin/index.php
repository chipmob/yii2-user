<?php

use chipmob\user\models\User;
use yii\bootstrap4\Html;
use yii\grid\GridView;

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var chipmob\user\models\search\UserSearch $searchModel
 */

$this->title = Yii::t('user', 'Manage users');
$this->params['breadcrumbs'][] = $this->title;

echo $this->render('@user/views/admin/_menu');

echo GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'columns' => [
        [
            'attribute' => 'id',
            'headerOptions' => ['style' => 'width: 5em;'],
        ],
        'username',
        'email:email',
        [
            'attribute' => 'created_at',
            'value' => fn(User $model) => Yii::$app->formatter->asDatetime($model->created_at),
            'filter' => false,
        ],
        [
            'attribute' => 'action_at',
            'value' => fn(User $model) => empty($model->action_at) ? Yii::t('user', 'Never') : Yii::$app->formatter->asDatetime($model->action_at),
            'filter' => false,
        ],
        [
            'header' => Yii::t('user', 'Confirmation status'),
            'headerOptions' => ['style' => 'width: 10em;'],
            'value' => function (User $model) {
                if ($model->isRemoved) return Html::tag('div', Yii::t('user', 'Removed'), ['class' => 'text-danger text-center']);
                if ($model->isConfirmed) {
                    return Html::tag('div', Yii::t('user', 'Confirmed'), ['class' => 'text-success text-center']);
                } else {
                    return Html::a(Yii::t('user', 'Confirm'), ['confirm', 'id' => $model->id], [
                        'class' => 'btn btn-block btn-xs btn-success',
                        'data-method' => 'post',
                        'data-confirm' => Yii::t('user', 'Are you sure you want to confirm this user?'),
                    ]);
                }
            },
            'filter' => Html::activeDropDownList($searchModel, 'is_confirmed',
                [
                    '1' => Yii::t('user', 'Yes'),
                    '0' => Yii::t('user', 'No'),
                ],
                [
                    'prompt' => Yii::t('user', 'Not selected'),
                    'class' => 'form-control',
                ]
            ),
            'format' => 'raw',
            'visible' => $searchModel->module->enableConfirmation,
        ],
        [
            'header' => Yii::t('user', 'Block status'),
            'headerOptions' => ['style' => 'width: 10em;'],
            'value' => function (User $model) {
                if ($model->isRemoved) return Html::tag('div', Yii::t('user', 'Removed'), ['class' => 'text-danger text-center']);
                if ($model->isBlocked) {
                    return Html::a(Yii::t('user', 'Unblock'), ['block', 'id' => $model->id], [
                        'class' => 'btn btn-xs btn-success btn-block',
                        'data-method' => 'post',
                        'data-confirm' => Yii::t('user', 'Are you sure you want to unblock this user?'),
                    ]);
                } else {
                    return Html::a(Yii::t('user', 'Block'), ['block', 'id' => $model->id], [
                        'class' => 'btn btn-block btn-xs btn-warning',
                        'data-method' => 'post',
                        'data-confirm' => Yii::t('user', 'Are you sure you want to block this user?'),
                    ]);
                }
            },
            'filter' => Html::activeDropDownList($searchModel, 'is_blocked',
                [
                    '1' => Yii::t('user', 'Yes'),
                    '0' => Yii::t('user', 'No'),
                ],
                [
                    'prompt' => Yii::t('user', 'Not selected'),
                    'class' => 'form-control',
                ]
            ),
            'format' => 'raw',
        ],
        [
            'header' => Yii::t('user', 'Removing status'),
            'headerOptions' => ['style' => 'width: 10em;'],
            'value' => function (User $model) {
                if ($model->isRemoved) {
                    return Html::a(Yii::t('user', 'Restore'), ['remove', 'id' => $model->id], [
                        'class' => 'btn btn-xs btn-success btn-block',
                        'data-method' => 'post',
                        'data-confirm' => Yii::t('user', 'Are you sure you want to restore this user?'),
                    ]);
                } else {
                    return Html::a(Yii::t('user', 'Remove'), ['remove', 'id' => $model->id], [
                        'class' => 'btn btn-block btn-xs btn-danger',
                        'data-method' => 'post',
                        'data-confirm' => Yii::t('user', 'Are you sure you want to remove this user?'),
                    ]);
                }
            },
            'filter' => Html::activeDropDownList($searchModel, 'is_removed',
                [
                    '1' => Yii::t('user', 'Yes'),
                    '0' => Yii::t('user', 'No'),
                ],
                [
                    'prompt' => Yii::t('user', 'Not selected'),
                    'class' => 'form-control',
                ]
            ),
            'format' => 'raw',
        ],
        [
            'class' => 'yii\grid\ActionColumn',
            'template' => '{switch} {resend_password} {log} {action} {update} {delete}',
            'buttons' => [
                'switch' => function ($url, User $model, $key) use ($searchModel) {
                    return Html::a(Html::tag('i', null, ['class' => 'fas fa-user-secret', 'aria-hidden' => true]), ['switch', 'id' => $model->id], [
                        'title' => Yii::t('user', 'Become this user'),
                        'data-confirm' => Yii::t('user', 'Are you sure you want to switch to this user for the rest of this Session?'),
                        'data-method' => 'post',
                    ]);
                },
                'resend_password' => function ($url, User $model, $key) {
                    return Html::a(Html::tag('i', null, ['class' => 'far fa-envelope', 'aria-hidden' => true]), ['resend-password', 'id' => $model->id], [
                        'title' => Yii::t('user', 'Generate and send new password to user'),
                        'data-confirm' => Yii::t('user', 'Are you sure?'),
                        'data-method' => 'post',
                    ]);
                },
                'log' => function ($url, User $model, $key) {
                    return Html::a(Html::tag('i', null, ['class' => 'far fa-list-alt', 'aria-hidden' => true]), ['log', 'id' => $model->id], [
                        'title' => Yii::t('user', 'Log list'),
                        'target' => '_blank',
                        'data-pjax' => 0,
                    ]);
                },
                'action' => function ($url, User $model, $key) {
                    return Html::a(Html::tag('i', null, ['class' => 'fas fa-history', 'aria-hidden' => true]), ['action', 'id' => $model->id], [
                        'title' => Yii::t('user', 'Action list'),
                        'target' => '_blank',
                        'data-pjax' => 0,
                    ]);
                },
            ],
            'visibleButtons' => [
                'switch' => fn(User $model) => $model->id != Yii::$app->user->id && !$model->isRemoved && !$model->isBlocked && $searchModel->module->enableImpersonateUser,
                'resend_password' => fn(User $model) => !$model->isRemoved && !$model->isAdmin, // TODO: checks everyone for isAdmin
            ],
        ],
    ],
]);
