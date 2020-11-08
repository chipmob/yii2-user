<?php

namespace chipmob\user\models;

use chipmob\user\components\behaviors\UserActionBehavior;
use chipmob\user\components\behaviors\UserBehavior;
use chipmob\user\components\behaviors\UserLogBehavior;
use chipmob\user\components\helpers\Password;
use chipmob\user\components\traits\ModuleTrait;
use chipmob\user\Mailer;
use chipmob\user\models\query\UserQuery;
use chipmob\user\Module;
use Throwable;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\web\IdentityInterface;

/**
 * User ActiveRecord model.
 *
 * @property integer $id
 * @property string $username
 * @property string $email
 * @property string $password_hash
 * @property string $auth_key
 * @property string $access_token
 * @property string $totp_key
 * @property integer $action_at
 * @property integer $confirmed_at
 * @property integer $blocked_at
 * @property integer $removed_at
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property Profile $profile
 *
 * @property Account[] $accounts Connected accounts ($provider => $account)
 * @property bool $isAdmin
 * @property bool $isTotp
 * @property bool $isConfirmed
 * @property bool $isBlocked
 * @property bool $isRemoved
 * @property string $name
 * @property string $password Plain password. Used for model validation.
 *
 * @property-read Module $module
 */
class User extends ActiveRecord implements IdentityInterface
{
    use ModuleTrait;

    const SCENARIO_CREATE = 'create';
    const SCENARIO_REGISTER = 'register';
    const SCENARIO_CONNECT = 'connect';
    const SCENARIO_UPDATE = 'update';
    const SCENARIO_SETTINGS = 'settings';

    const EVENT_IMPERSONATE = 'impersonate';
    const EVENT_SECURITY = 'security';

    /** Name of the session key in which the original user id is saved when using the impersonate user function. */
    const ORIGINAL_USER_SESSION_KEY = 'original_user';

    const OAUTH_SESSION_KEY = 'oauth_user_id';
    const TOTP_SESSION_KEY = 'totp_user_id';

    public static string $usernameRegexp = '/^[-a-zA-Z0-9_\.@]+$/';
    public static string $passwordRegexp = YII_DEBUG ? '/.+/' : '^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])([a-zA-Z0-9])+$';
    public static int $minLoginLength = YII_DEBUG ? 4 : 6;
    public static int $minPasswordLength = YII_DEBUG ? 4 : 8;

    public ?string $password = null;

    /** @inheritdoc */
    public static function tableName()
    {
        return '{{%user}}';
    }

    /** @inheritdoc */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
            UserBehavior::class,
            UserActionBehavior::class,
            UserLogBehavior::class,
        ];
    }

    /** @inheritdoc */
    public function rules()
    {
        return [
            [['username', 'email'], 'trim'],
            [['username', 'email'], 'required'],
            [['username', 'email'], 'string', 'min' => self::$minLoginLength, 'max' => 255],
            ['username', 'match', 'pattern' => static::$usernameRegexp],
            ['email', 'email'],
            ['username', 'unique', 'message' => Yii::t('user', 'This username has already been taken')],
            ['email', 'unique', 'message' => Yii::t('user', 'This email address has already been taken')],
            ['password', 'required', 'skipOnEmpty' => $this->module->enableGeneratingPassword, 'on' => [self::SCENARIO_REGISTER]],
            ['password', 'string', 'min' => static::$minPasswordLength, 'max' => 72],
            ['password', 'match', 'pattern' => static::$passwordRegexp],
            ['password', 'default', 'value' => Password::generate(8), 'on' => [self::SCENARIO_CREATE, self::SCENARIO_REGISTER, self::SCENARIO_CONNECT]],
            ['access_token', 'unique'],
            [['confirmed_at'], 'default', 'value' => time(), 'skipOnEmpty' => $this->module->enableConfirmation, 'on' => [self::SCENARIO_CREATE, self::SCENARIO_REGISTER]],
            [['action_at', 'confirmed_at', 'blocked_at', 'removed_at'], 'default', 'value' => null],
        ];
    }

    /** @inheritdoc */
    public function scenarios()
    {
        return ArrayHelper::merge(parent::scenarios(), [
            self::SCENARIO_CREATE => ['username', 'email', 'password'],
            self::SCENARIO_REGISTER => ['username', 'email', 'password'],
            self::SCENARIO_CONNECT => ['username', 'email'],
            self::SCENARIO_UPDATE => ['username', 'password'],
            self::SCENARIO_SETTINGS => ['username', 'password'],
        ]);
    }

    /** @inheritdoc */
    public function attributeLabels()
    {
        return [
            'username' => Yii::t('user', 'Username'),
            'email' => Yii::t('user', 'Email'),
            'access_token' => Yii::t('user', 'API key'),
            'action_at' => Yii::t('user', 'Last action'),
            'confirmed_at' => Yii::t('user', 'Confirmed at'),
            'blocked_at' => Yii::t('user', 'Blocked at'),
            'removed_at' => Yii::t('user', 'Removed at'),
            'created_at' => Yii::t('user', 'Created at'),
            'updated_at' => Yii::t('user', 'Updated at'),
            'password' => Yii::t('user', 'Password'),
        ];
    }

    /** @inheritdoc */
    public function transactions()
    {
        return [
            self::SCENARIO_CREATE => self::OP_INSERT,
            self::SCENARIO_REGISTER => self::OP_INSERT,
        ];
    }

    public function getProfile(): ActiveQuery
    {
        return $this->hasOne($this->module->modelMap['Profile'], ['user_id' => 'id']);
    }

    public function getAccounts(): array
    {
        $connected = [];
        $accounts = $this->hasMany($this->module->modelMap['Account'], ['user_id' => 'id'])->all();

        /** @var Account $account */
        foreach ($accounts as $account) {
            $connected[$account->provider] = $account;
        }

        return $connected;
    }

    public function getIsAdmin(): bool
    {
        return Yii::$app->authManager && $this->module->adminPermission ? Yii::$app->authManager->checkAccess($this->id, $this->module->adminPermission) : false;
    }

    public function getIsTotp(): bool
    {
        return $this->totp_key != null;
    }

    public function getIsConfirmed(): bool
    {
        return $this->confirmed_at != null;
    }

    public function getIsBlocked(): bool
    {
        return $this->blocked_at != null;
    }

    public function getIsRemoved(): bool
    {
        return $this->removed_at != null;
    }

    public function getName(): string
    {
        return $this->profile->name ?? $this->username;
    }

    public function create(): bool
    {
        if ($this->isNewRecord == false || !$this->validate()) {
            return false;
        }

        try {
            return $this->save();
        } catch (Throwable $e) {
            Yii::warning($e->getMessage());
            return false;
        }
    }

    public function createToken(string $type): Token
    {
        $token = Yii::createObject(['class' => Token::class, 'type' => $type]);
        $token->link('user', $this);
        return $token;
    }

    public function confirm(): bool
    {
        if ($this->isConfirmed) return true;

        $this->confirmed_at = time();
        return $this->save(false, ['confirmed_at']);
    }

    public function block(): bool
    {
        $this->blocked_at = time();
        return $this->save(false, ['blocked_at']);
    }

    public function unblock(): bool
    {
        $this->blocked_at = null;
        return $this->save(false, ['blocked_at']);
    }

    public function remove(): bool
    {
        $this->removed_at = time();
        return $this->save(false, ['removed_at']);
    }

    public function restore(): bool
    {
        $this->removed_at = null;
        return $this->save(false, ['removed_at']);
    }

    public function resetPassword(): bool
    {
        return $this->save(false, ['password_hash']);
    }

    public function resendPassword(): bool
    {
        $this->password = Password::generate(8);
        if ($this->save(false, ['password_hash'])) {
            Yii::$container->get(Mailer::class)->sendGeneratedPassword($this, ['password' => $this->password]);
            return true;
        }
        return false;
    }

    public static function generateAccessToken(): string
    {
        $model = Yii::createObject(self::class);
        do {
            $model->setAttribute('access_token', Yii::$app->security->generateRandomString());
        } while (!$model->validate('access_token'));
        return $model->getAttribute('access_token');
    }

    /** @inheritdoc */
    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    /** @inheritdoc */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['access_token' => $token]);
    }

    /** @inheritdoc */
    public function getId()
    {
        return $this->getAttribute('id');
    }

    /** @inheritdoc */
    public function getAuthKey()
    {
        return $this->getAttribute('auth_key');
    }

    /** @inheritdoc */
    public function validateAuthKey($authKey)
    {
        return $this->getAttribute('auth_key') === $authKey;
    }

    public static function find(): ActiveQuery
    {
        return Yii::createObject(UserQuery::class, [get_called_class()]);
    }
}
