<?php

namespace chipmob\user\components\widgets;

use chipmob\user\models\User;
use Yii;
use yii\bootstrap4\Nav;
use yii\bootstrap4\Widget;

/**
 * Admin menu widget.
 */
class AdminMenu extends Widget
{
    public array $items = [];

    public User $user;

    /** @inheritdoc */
    public function init()
    {
        parent::init();

        $this->items = [
            'account' => [
                'label' => Yii::t('user', 'Account details'),
                'url' => ['/user/admin/update', 'id' => $this->user->id]
            ],
            'profile' => [
                'label' => Yii::t('user', 'Profile details'),
                'url' => ['/user/admin/update-profile', 'id' => $this->user->id]
            ],
            '<hr>',
            'confirm' => [
                'label' => Yii::t('user', 'Confirm'),
                'url' => ['/user/admin/confirm', 'id' => $this->user->id],
                'visible' => !$this->user->isConfirmed && !$this->user->isRemoved,
                'linkOptions' => [
                    'class' => 'text-success',
                    'data-method' => 'post',
                    'data-confirm' => Yii::t('user', 'Are you sure you want to confirm this user?'),
                ],
            ],
            'block' => [
                'label' => Yii::t('user', 'Block'),
                'url' => ['/user/admin/block', 'id' => $this->user->id],
                'visible' => !$this->user->isBlocked && !$this->user->isRemoved,
                'linkOptions' => [
                    'class' => 'text-warning',
                    'data-method' => 'post',
                    'data-confirm' => Yii::t('user', 'Are you sure you want to block this user?'),
                ],
            ],
            'unblock' => [
                'label' => Yii::t('user', 'Unblock'),
                'url' => ['/user/admin/block', 'id' => $this->user->id],
                'visible' => $this->user->isBlocked && !$this->user->isRemoved,
                'linkOptions' => [
                    'class' => 'text-success',
                    'data-method' => 'post',
                    'data-confirm' => Yii::t('user', 'Are you sure you want to unblock this user?'),
                ],
            ],
            'remove' => [
                'label' => Yii::t('user', 'Remove'),
                'url' => ['/user/admin/remove', 'id' => $this->user->id],
                'visible' => $this->user->id != Yii::$app->user->id && !$this->user->isRemoved,
                'linkOptions' => [
                    'class' => 'text-danger',
                    'data-method' => 'post',
                    'data-confirm' => Yii::t('user', 'Are you sure you want to remove this user?'),
                ],
            ],
            'restore' => [
                'label' => Yii::t('user', 'Restore'),
                'url' => ['/user/admin/remove', 'id' => $this->user->id],
                'visible' => $this->user->id != Yii::$app->user->id && $this->user->isRemoved,
                'linkOptions' => [
                    'class' => 'text-success',
                    'data-method' => 'post',
                    'data-confirm' => Yii::t('user', 'Are you sure you want to restore this user?'),
                ],
            ],
            'delete' => [
                'label' => Yii::t('user', 'Delete'),
                'url' => ['/user/admin/delete', 'id' => $this->user->id],
                'visible' => $this->user->id != Yii::$app->user->id,
                'linkOptions' => [
                    'class' => 'text-danger',
                    'data-method' => 'post',
                    'data-confirm' => Yii::t('user', 'Are you sure you want to delete this user?'),
                ],
            ],
        ];
    }

    /** @inheritdoc */
    public function run()
    {
        return Nav::widget([
            'options' => ['class' => 'nav-pills flex-column'],
            'items' => $this->items,
        ]);
    }
}
