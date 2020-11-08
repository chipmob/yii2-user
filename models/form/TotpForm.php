<?php

namespace chipmob\user\models\form;

use chipmob\user\components\GoogleAuthenticator;
use chipmob\user\components\traits\UserTrait;
use Yii;
use yii\base\Model;

class TotpForm extends Model
{
    use UserTrait;

    public ?string $totp_code = null;

    private GoogleAuthenticator $_ga;

    /** @inheritdoc */
    public function init()
    {
        parent::init();

        $this->_ga = new GoogleAuthenticator();
    }

    /** @inheritdoc */
    public function formName()
    {
        return 'totp-form';
    }

    /** @inheritdoc */
    public function rules()
    {
        return [
            ['totp_code', 'required'],
            ['totp_code', 'validateCode'],
        ];
    }

    /** @inheritdoc */
    public function attributeLabels()
    {
        return [
            'totp_key' => Yii::t('user', 'Secret code'),
            'totp_code' => Yii::t('user', 'Confirmation code'),
        ];
    }

    public function validateCode(string $attribute)
    {
        if (!$this->_ga->verifyCode($this->user->totp_key, $this->totp_code)) {
            $this->addError($attribute, Yii::t('user', 'Confirmation code does not match'));
        }
    }
}
