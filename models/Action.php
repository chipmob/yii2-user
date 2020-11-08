<?php

namespace chipmob\user\models;

use chipmob\user\components\traits\ModuleTrait;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\web\UserEvent;

/**
 * Action Active Record model.
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $type
 * @property string $ip
 * @property string $ua
 * @property integer $created_at
 *
 * @property User $user
 */
class Action extends ActiveRecord
{
    use ModuleTrait;

    const TYPE_CREATE = 1;
    const TYPE_REGISTER = 2;
    const TYPE_LOGIN = 3;
    const TYPE_COOKIE = 4;
    const TYPE_OAUTH = 5;
    const TYPE_LOGOUT = 6;
    const TYPE_TOKEN = 7;
    const TYPE_SWITCH = 8;
    const TYPE_RETURN = 9;

    /** @inheritdoc */
    public static function tableName()
    {
        return '{{%user_action}}';
    }

    /** @inheritdoc */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'updatedAtAttribute' => false,
            ],
        ];
    }

    /** @inheritdoc */
    public function attributeLabels()
    {
        return [
            'ip' => Yii::t('user', 'IP address'),
            'ua' => Yii::t('user', 'User agent'),
            'type' => Yii::t('user', 'Action type'),
            'created_at' => Yii::t('user', 'Action time'),
        ];
    }

    public function getUser(): ActiveQuery
    {
        return $this->hasOne($this->module->modelMap['User'], ['id' => 'user_id']);
    }

    public static function saveLogin(UserEvent $event)
    {
        /** @var User $user */
        if ($user = $event->identity) {
            $user->touch('action_at');
            if (Yii::$app->session->has(User::OAUTH_SESSION_KEY)) {
                $type = self::TYPE_OAUTH;
                Yii::$app->session->remove(User::OAUTH_SESSION_KEY);
            } else {
                $type = $event->cookieBased ? self::TYPE_COOKIE : self::TYPE_LOGIN;
            }
            $visit = Yii::createObject([
                'class' => static::class,
                'ip' => Yii::$app->request->userIP,
                'ua' => Yii::$app->request->userAgent,
                'type' => $type,
            ]);
            $visit->link('user', $user);
        }
    }

    public static function saveLogout(UserEvent $event)
    {
        /** @var User $user */
        if ($user = $event->identity) {
            $user->touch('action_at');
            $visit = Yii::createObject([
                'class' => static::class,
                'ip' => Yii::$app->request->userIP,
                'ua' => Yii::$app->request->userAgent,
                'type' => self::TYPE_LOGOUT,
            ]);
            $visit->link('user', $user);
        }
    }

    public static function getListOfActions(): array
    {
        return [
            self::TYPE_CREATE => Yii::t('user', 'Creation'),
            self::TYPE_REGISTER => Yii::t('user', 'Registration'),
            self::TYPE_LOGIN => Yii::t('user', 'Login form'),
            self::TYPE_COOKIE => Yii::t('user', 'Login cookie'),
            self::TYPE_OAUTH => Yii::t('user', 'Login oauth'),
            self::TYPE_LOGOUT => Yii::t('user', 'Logout action'),
            self::TYPE_TOKEN => Yii::t('user', 'Login API'),
            self::TYPE_SWITCH => Yii::t('user', 'Impersonate switch'),
            self::TYPE_RETURN => Yii::t('user', 'Impersonate return'),
        ];
    }
}
