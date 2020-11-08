<?php

namespace chipmob\user\models;

use chipmob\user\components\traits\ModuleTrait;
use chipmob\user\models\query\TokenQuery;
use chipmob\user\Module;
use RuntimeException;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\Url;

/**
 * Token Active Record model.
 *
 * @property integer $user_id
 * @property string $code
 * @property integer $type
 * @property integer $expired_at
 *
 * @property User $user
 *
 * @property string $url
 *
 * @property-read Module $module
 */
class Token extends ActiveRecord
{
    use ModuleTrait;

    const TYPE_CONFIRMATION = 0;
    const TYPE_RECOVERY = 1;

    private array $_urlParams = [];

    /** @inheritdoc */
    public static function tableName()
    {
        return '{{%user_token}}';
    }

    /** @inheritdoc */
    public static function primaryKey()
    {
        return ['user_id', 'code', 'type'];
    }

    public function getUser(): ActiveQuery
    {
        return $this->hasOne($this->module->modelMap['User'], ['id' => 'user_id']);
    }

    public function getUrl(): string
    {
        switch ($this->type) {
            case self::TYPE_CONFIRMATION:
                $route = '/user/registration/confirm';
                break;
            case self::TYPE_RECOVERY:
                $route = '/user/recovery/reset';
                break;
            default:
                throw new RuntimeException();
        }

        return Url::to([$route, 'id' => $this->user_id, 'code' => $this->code], true);
    }

    /** @inheritdoc */
    public function beforeSave($insert)
    {
        if ($insert) {
            static::deleteAll(['user_id' => $this->user_id, 'type' => $this->type]);

            switch ($this->type) {
                case self::TYPE_CONFIRMATION:
                    $this->setAttribute('expired_at', $this->module->confirmWithin + time());
                    break;
                case self::TYPE_RECOVERY:
                    $this->setAttribute('expired_at', $this->module->recoverWithin + time());
                    break;
                default:
                    throw new RuntimeException();
            }

            $this->setAttribute('code', Yii::$app->security->generateRandomString());
        }

        return parent::beforeSave($insert);
    }

    public static function find(): ActiveQuery
    {
        return Yii::createObject(TokenQuery::class, [get_called_class()]);
    }
}
