<?php

namespace chipmob\user\components\behaviors;

use chipmob\user\components\traits\ModuleTrait;
use chipmob\user\models\Action;
use chipmob\user\models\User;
use Yii;
use yii\base\Behavior;
use yii\base\Event;
use yii\db\ActiveRecord;
use yii\db\AfterSaveEvent;

class UserActionBehavior extends Behavior
{
    use ModuleTrait;

    /** @var User */
    public $owner;

    /** @inheritdoc */
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_INSERT => 'afterInsert',
            User::EVENT_IMPERSONATE => 'afterSwitch',
        ];
    }

    public function afterInsert(AfterSaveEvent $event)
    {
        switch ($this->owner->scenario) {
            case User::SCENARIO_CREATE:
                $type = Action::TYPE_CREATE;
                break;
            case User::SCENARIO_REGISTER:
                $type = Action::TYPE_REGISTER;
                break;
            default:
                return;
        }
        $action = Yii::createObject([
            'class' => Action::class,
            'ip' => Yii::$app->request instanceof \yii\web\Request ? Yii::$app->request->userIP : (gethostname() ? gethostbyname(gethostname()) : '127.0.0.1'),
            'ua' => Yii::$app->request instanceof \yii\web\Request ? Yii::$app->request->userAgent : php_sapi_name(),
            'type' => $type,
        ]);
        $action->link('user', $this->owner);
    }

    public function afterSwitch(Event $event)
    {
        if (Yii::$app->session->has(User::ORIGINAL_USER_SESSION_KEY)) {
            $user = $this->owner::findOne((int)Yii::$app->session->get(User::ORIGINAL_USER_SESSION_KEY));
            $action = Yii::createObject([
                'class' => Action::class,
                'ip' => Yii::$app->request->userIP,
                'ua' => Yii::$app->request->userAgent,
                'type' => Action::TYPE_SWITCH,
            ]);
            $action->link('user', $user);
        } else {
            $user = $this->owner;
            $action = Yii::createObject([
                'class' => Action::class,
                'ip' => Yii::$app->request->userIP,
                'ua' => Yii::$app->request->userAgent,
                'type' => Action::TYPE_RETURN,
            ]);
            $action->link('user', $user);
        }
        $user->touch('action_at');
    }
}
