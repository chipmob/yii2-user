<?php

namespace chipmob\user\components\widgets;

use Yii;
use yii\authclient\ClientInterface;
use yii\authclient\widgets\AuthChoice;
use yii\authclient\widgets\AuthChoiceAsset;
use yii\bootstrap4\Html;
use yii\helpers\Url;

class Connect extends AuthChoice
{
    public array $accounts = [];

    public string $clientsContainer = <<<HTML
<div class="d-flex align-items-center border-top p-2">
    <div class="mr-3 ml-0 auth-icon {class}"></div>
    <div class="mr-auto text-bold">{title}</div>
    {button}
</div>
HTML;

    public string $customCss = <<<CSS
.auth-clients {
    margin: 0 -1em;
    padding: 0 1em;
}
.auth-clients li {
    margin: 0 1em 1em 0;
}
CSS;

    /** @inheritdoc */
    public function init()
    {
        $id = $this->id;
        $view = Yii::$app->view;
        AuthChoiceAsset::register($view);
        if ($this->popupMode) {
            $view->registerJs("jQuery('#$id').authchoice();");
        }
        if (Yii::$app->user->isGuest) {
            $view->registerCss($this->customCss);
        }
        $this->options['id'] = $id;
        echo Html::beginTag('div', $this->options);
    }

    /** @inheritdoc */
    protected function renderMainContent()
    {
        if (Yii::$app->user->isGuest) {
            return parent::renderMainContent();
        }

        $content = '';
        foreach ($this->getClients() as $client) {
            $button = $this->isConnected($client)
                ? Html::a(Yii::t('user', 'Disconnect'), $this->createClientUrl($client), ['class' => 'btn btn-danger', 'data-method' => 'post'])
                : Html::a(Yii::t('user', 'Connect'), $this->createClientUrl($client), ['class' => 'btn btn-success']);
            $content .= str_replace(['{class}', '{title}', '{button}'], [$client->name, $client->title, $button], $this->clientsContainer);
        }
        return $content;
    }

    public function isConnected(ClientInterface $provider): bool
    {
        return isset($this->accounts[$provider->id]);
    }

    /** @inheritdoc */
    public function createClientUrl($provider)
    {
        $this->autoRender = false;
        if ($this->isConnected($provider)) {
            return Url::to(['/user/settings/disconnect', 'id' => $this->accounts[$provider->id]->id]);
        } else {
            return parent::createClientUrl($provider);
        }
    }
}
