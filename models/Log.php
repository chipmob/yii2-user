<?php

namespace chipmob\user\models;

use chipmob\user\components\traits\ModuleTrait;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Action Active Record model.
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $data
 * @property string $ip
 * @property string $ua
 * @property integer $created_at
 * @property integer $created_by
 *
 * @property User $user
 * @property User $author
 */
class Log extends ActiveRecord
{
    use ModuleTrait;

    /** @inheritdoc */
    public static function tableName()
    {
        return '{{%user_log}}';
    }

    /** @inheritdoc */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'updatedAtAttribute' => false,
            ],
            [
                'class' => BlameableBehavior::class,
                'updatedByAttribute' => false,
            ],
        ];
    }

    /** @inheritdoc */
    public function attributeLabels()
    {
        return [
            'ip' => Yii::t('user', 'IP address'),
            'ua' => Yii::t('user', 'User agent'),
            'data' => Yii::t('user', 'Data'),
            'created_at' => Yii::t('user', 'Created at'),
            'created_by' => Yii::t('user', 'Author'),
        ];
    }

    public function getUser(): ActiveQuery
    {
        return $this->hasOne($this->module->modelMap['User'], ['id' => 'user_id']);
    }

    public function getAuthor(): ActiveQuery
    {
        return $this->hasOne($this->module->modelMap['User'], ['id' => 'created_by'])->alias('author');
    }

    public static function getListOfAuthors(): array
    {
        return static::find()
            ->innerJoinWith('author')
            ->select(['author.username', 'author.id'])
            ->groupBy('author.id')
            ->indexBy('id')
            ->column();
    }
}
