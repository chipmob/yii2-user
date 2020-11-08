<?php

namespace chipmob\user\components\widgets;

use Yii;
use yii\bootstrap4\Nav;
use yii\bootstrap4\Widget;

/**
 * User menu widget.
 */
class UserMenu extends Widget
{
    public array $items = [];

    /** @inheritdoc */
    public function init()
    {
        parent::init();

        $networksVisible = Yii::$app->has('authClientCollection') ? count(Yii::$app->get('authClientCollection')->clients) > 0 : false;

        $this->items = [
            ['label' => Yii::t('user', 'Profile'), 'url' => ['/user/settings/profile']],
            ['label' => Yii::t('user', 'Account'), 'url' => ['/user/settings/account']],
            ['label' => Yii::t('user', 'API key'), 'url' => ['/user/settings/api-key']],
            ['label' => Yii::t('user', 'Google TOTP'), 'url' => ['/user/settings/google-totp']],
            ['label' => Yii::t('user', 'Networks'), 'url' => ['/user/settings/networks'], 'visible' => $networksVisible],
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
