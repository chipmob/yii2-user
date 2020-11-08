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

    /** @inheritdoc */
    public function init()
    {
        AuthChoiceAsset::register(Yii::$app->view);
        if ($this->popupMode) {
            Yii::$app->view->registerJs("\$('#" . $this->id . "').authchoice();");
        }
        $this->options['id'] = $this->id;
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
