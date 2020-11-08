<?php

namespace chipmob\user\components\behaviors;

use chipmob\user\components\traits\ModuleTrait;
use chipmob\user\models\Log;
use chipmob\user\models\User;
use Yii;
use yii\base\Behavior;
use yii\db\ActiveRecord;
use yii\db\AfterSaveEvent;
use yii\helpers\Json;

class UserLogBehavior extends Behavior
{
    use ModuleTrait;

    /** @var User */
    public $owner;

    /** @inheritdoc */
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_UPDATE => 'afterUpdate',
        ];
    }

    public function afterUpdate(AfterSaveEvent $event)
    {
        $data = $event->changedAttributes;
        unset($data['action_at'], $data['created_at'], $data['updated_at']);
        foreach ($data as $name => &$value) {
            if (in_array($name, ['password_hash', 'auth_key', 'access_token'])) {
                $value = 'MOD';
            }
            if (in_array($name, ['totp_key', 'confirmed_at', 'blocked_at', 'removed_at'])) {
                $value = empty($value) ? 'ON' : 'OFF';
            }
        }
        if (empty($data)) return;

        $log = Yii::createObject([
            'class' => Log::class,
            'ip' => Yii::$app->request instanceof \yii\web\Request ? Yii::$app->request->userIP : (gethostname() ? gethostbyname(gethostname()) : '127.0.0.1'),
            'ua' => Yii::$app->request instanceof \yii\web\Request ? Yii::$app->request->userAgent : php_sapi_name(),
            'data' => Json::encode($data),
        ]);
        $log->link('user', $this->owner);
    }
}
