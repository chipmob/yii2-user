<?php

namespace chipmob\user\models;

use chipmob\user\components\traits\ModuleTrait;
use chipmob\user\models\query\AccountQuery;
use Yii;
use yii\authclient\ClientInterface;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\Json;

/**
 * Account Active Record model.
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $provider
 * @property string $client_id
 * @property string $data
 * @property string $email
 * @property string $username
 * @property integer $created_at
 *
 * @property User $user
 *
 * @property bool $isConnected
 * @property string $decodedData
 */
class Account extends ActiveRecord
{
    use ModuleTrait;

    const CONNECT_SESSION_KEY = 'connect_account_id';

    private ?string $_data = null;

    /** @inheritdoc */
    public static function tableName()
    {
        return '{{%user_account}}';
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

    public function getUser(): ActiveQuery
    {
        return $this->hasOne($this->module->modelMap['User'], ['id' => 'user_id']);
    }

    public function getIsConnected(): bool
    {
        return $this->user_id != null;
    }

    public function getDecodedData(): string
    {
        return $this->_data ??= Json::decode($this->data);
    }

    public function process(User $user): bool
    {
        $this->link('user', $user);
        if ($this->email == $user->email) {
            return $user->confirm();
        }
        return false;
    }

    public static function create(ClientInterface $client): self
    {
        $account = static::createFromClient($client);
        if (($user = static::fetchUser($account)) instanceof User) {
            $account->user_id = $user->id;
        }
        $account->save(false);
        return $account;
    }

    protected static function fetchUser(Account $account): ?User
    {
        /** @var User $user */
        $user = Yii::$container->get('UserQuery')->byEmail((string)$account->email)->one();
        if (empty($user)) {
            $user = Yii::createObject([
                'class' => User::class,
                'scenario' => User::SCENARIO_CONNECT,
                'username' => $account->username,
                'email' => $account->email,
            ]);
            $emailValid = $user->validate(['email']);
            $usernameValid = $user->validate(['username']);
            if (!$emailValid) {
                $account->email = null;
            }
            if (!$usernameValid) {
                $account->username = null;
            }
            if ($emailValid && $usernameValid) {
                return $user->create() ? $user : null;
            }
        }
        return $user;
    }

    public static function connect(ClientInterface $client)
    {
        $account = static::fetchAccount($client);
        if (empty($account->user)) {
            /** @var User $user */
            $user = Yii::$app->user->identity;
            $account->link('user', $user);
            Yii::$app->session->setFlash('success', Yii::t('user', 'Your account has been connected'));
        } else {
            Yii::$app->session->setFlash('danger', Yii::t('user', 'This account has already been connected to another user'));
        }
    }

    protected static function fetchAccount(ClientInterface $client): self
    {
        $account = static::find()->byClient($client)->one();
        if (empty($account)) {
            $account = static::createFromClient($client);
            $account->save(false);
        }
        return $account;
    }

    protected static function createFromClient(ClientInterface $client): self
    {
        $account = Yii::createObject([
            'class' => static::class,
            'provider' => $client->id,
            'client_id' => $client->userAttributes['id'],
            'data' => Json::encode($client->userAttributes),
        ]);
        if ($client instanceof \chipmob\user\components\clients\ClientInterface) {
            $account->setAttributes([
                'username' => $client->username,
                'email' => $client->email,
            ], false);
        }
        return $account;
    }

    public static function find(): ActiveQuery
    {
        return Yii::createObject(AccountQuery::class, [get_called_class()]);
    }
}
