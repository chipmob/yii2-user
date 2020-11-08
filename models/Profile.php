<?php

namespace chipmob\user\models;

use chipmob\user\components\helpers\Timezone;
use chipmob\user\components\traits\ModuleTrait;
use DateTime;
use DateTimeZone;
use Exception;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * Profile Active Record model.
 *
 * @property integer $user_id
 * @property string $name
 * @property string $public_email
 * @property string $location
 * @property string $website
 * @property string $timezone
 *
 * @property User $user
 */
class Profile extends ActiveRecord
{
    use ModuleTrait;

    /** @inheritdoc */
    public static function tableName()
    {
        return '{{%user_profile}}';
    }

    /** @inheritdoc */
    public function rules()
    {
        return [
            [['name', 'public_email', 'location', 'website'], 'string', 'max' => 255],
            ['public_email', 'email'],
            ['website', 'url'],
            ['timezone', 'validateTimeZone'],
            [['name', 'public_email', 'location', 'website', 'timezone'], 'default', 'value' => null],
        ];
    }

    /** @inheritdoc */
    public function attributeLabels()
    {
        return [
            'name' => Yii::t('user', 'Name'),
            'public_email' => Yii::t('user', 'Email (public)'),
            'location' => Yii::t('user', 'Location'),
            'website' => Yii::t('user', 'Website'),
            'timezone' => Yii::t('user', 'Time zone'),
        ];
    }

    public function getUser(): ActiveQuery
    {
        return $this->hasOne($this->module->modelMap['User'], ['id' => 'user_id']);
    }

    public function validateTimeZone(?string $attribute)
    {
        if (!in_array($this->$attribute, timezone_identifiers_list())) {
            $this->addError($attribute, Yii::t('user', 'Time zone is not valid'));
        }
    }

    public function setTimeZone(DateTimeZone $timeZone)
    {
        $this->setAttribute('timezone', $timeZone->getName());
    }

    public function getTimeZone(): DateTimeZone
    {
        try {
            return new DateTimeZone($this->timezone);
        } catch (Exception $e) {
            return new DateTimeZone(Yii::$app->timeZone);
        }
    }

    public function toLocalTime(DateTime $dateTime = null): DateTime
    {
        $dateTime ??= new DateTime();

        return $dateTime->setTimezone($this->getTimeZone());
    }

    public static function getTimezoneList(): array
    {
        return ArrayHelper::map(Timezone::getAll(), 'timezone', 'name');
    }

    /** @inheritdoc */
    public function afterFind()
    {
        parent::afterFind();

        $this->timezone = $this->getTimeZone()->getName();
    }
}
