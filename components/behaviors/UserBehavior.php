<?php

namespace chipmob\user\components\behaviors;

use chipmob\user\components\helpers\Password;
use chipmob\user\components\traits\ModuleTrait;
use chipmob\user\Mailer;
use chipmob\user\models\Profile;
use chipmob\user\models\Token;
use chipmob\user\models\User;
use Yii;
use yii\base\Behavior;
use yii\base\ModelEvent;
use yii\db\ActiveRecord;
use yii\db\AfterSaveEvent;

class UserBehavior extends Behavior
{
    use ModuleTrait;

    /** @var User */
    public $owner;

    /** @inheritdoc */
    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_INSERT => 'beforeInsert',
            ActiveRecord::EVENT_AFTER_INSERT => 'afterInsert',
            ActiveRecord::EVENT_BEFORE_UPDATE => 'beforeUpdate',
            ActiveRecord::EVENT_AFTER_UPDATE => 'afterUpdate',
        ];
    }

    public function beforeInsert(ModelEvent $event)
    {
        $this->owner->setAttribute('password_hash', Password::hash($this->owner->password));
        $this->owner->setAttribute('auth_key', Yii::$app->security->generateRandomString());
        $this->owner->setAttribute('access_token', User::generateAccessToken());
    }

    public function afterInsert(AfterSaveEvent $event)
    {
        $profile = Yii::createObject(Profile::class);
        $profile->link('user', $this->owner);

        if (in_array($this->owner->scenario, [User::SCENARIO_REGISTER]) && $this->module->enableConfirmation) {
            $token = $this->owner->createToken(Token::TYPE_CONFIRMATION);
        }
        Yii::$container->get(Mailer::class)->sendWelcomeMessage($this->owner, ['password' => $this->owner->password, 'token_url' => isset($token) ? $token->url : null]);
    }

    public function beforeUpdate(ModelEvent $event)
    {
        if (!empty($this->owner->password)) {
            $this->owner->setAttribute('password_hash', Password::hash($this->owner->password));
        }
    }

    public function afterUpdate(AfterSaveEvent $event)
    {
        if (array_key_exists('password_hash', $event->changedAttributes)) {
            $this->owner->updateAttributes(['auth_key' => Yii::$app->security->generateRandomString()]);
            $this->owner->updateAttributes(['access_token' => User::generateAccessToken()]);
            $this->owner->trigger(User::EVENT_SECURITY);
        }
        if (array_key_exists('totp_key', $event->changedAttributes) && $this->owner->isTotp) {
            $this->owner->updateAttributes(['auth_key' => Yii::$app->security->generateRandomString()]);
            $this->owner->trigger(User::EVENT_SECURITY);
        }
        if (array_key_exists('blocked_at', $event->changedAttributes) && $this->owner->isBlocked) {
            $this->owner->updateAttributes(['auth_key' => Yii::$app->security->generateRandomString()]);
            $this->owner->trigger(User::EVENT_SECURITY);
        }
        if (array_key_exists('removed_at', $event->changedAttributes) && $this->owner->isRemoved) {
            $this->owner->updateAttributes(['password_hash' => Password::hash(Yii::$app->security->generateRandomString())]);
            $this->owner->updateAttributes(['auth_key' => Yii::$app->security->generateRandomString()]);
            $this->owner->updateAttributes(['access_token' => User::generateAccessToken()]);
            $this->owner->updateAttributes(['totp_key' => null, 'confirmed_at' => null, 'blocked_at' => null]);
            $this->owner->trigger(User::EVENT_SECURITY);
        }
    }
}
