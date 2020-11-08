<?php

namespace chipmob\user\models\form;

use chipmob\user\components\GoogleAuthenticator;
use chipmob\user\components\traits\UserTrait;
use Yii;
use yii\base\Model;

/**
 * Class GoogleTotpForm
 *
 * @property string $imageSrc
 */
class GoogleTotpForm extends Model
{
    use UserTrait;

    public string $totp_key;
    public ?string $totp_code = null;

    private GoogleAuthenticator $_ga;

    /** @inheritdoc */
    public function init()
    {
        parent::init();

        $this->_ga = new GoogleAuthenticator();

        $this->totp_key = $this->user->isTotp ? $this->user->totp_key : $this->createSecret();
    }

    /** @inheritdoc */
    public function formName()
    {
        return 'google-totp-form';
    }

    /** @inheritdoc */
    public function rules()
    {
        return [
            ['totp_key', 'string'],
            ['totp_code', 'integer'],
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

    public function getImageSrc(): string
    {
        return $this->_ga->getQRCodeGoogleUrl(Yii::$app->name ?? 'WEB', $this->totp_key);
    }

    public function createSecret(): string
    {
        return $this->_ga->createSecret();
    }

    public function validateCode(string $attribute)
    {
        if (!$this->_ga->verifyCode($this->totp_key, $this->totp_code)) {
            $this->addError($attribute, Yii::t('user', 'Confirmation code does not match'));
        }
    }

    public function toggle(): bool
    {
        if ($this->totp_key === $this->user->totp_key) {
            $this->user->totp_key = null;
            return $this->user->save(false, ['totp_key']);
        }

        if ($this->validate()) {
            $this->user->totp_key = $this->totp_key;
            return $this->user->save(false, ['totp_key']);
        }

        return false;
    }
}
