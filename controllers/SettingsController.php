<?php

namespace chipmob\user\controllers;

use chipmob\user\components\traits\AjaxValidationTrait;
use chipmob\user\components\traits\ModuleTrait;
use chipmob\user\models\Account;
use chipmob\user\models\form\GoogleTotpForm;
use chipmob\user\models\form\SettingsForm;
use chipmob\user\models\Profile;
use chipmob\user\models\User;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * SettingsController manages updating user settings (e.g. profile, email and password).
 */
class SettingsController extends Controller
{
    use ModuleTrait;
    use AjaxValidationTrait;

    /** @inheritdoc */
    public $defaultAction = 'profile';

    /** @inheritdoc */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'disconnect' => ['post'],
                    'delete' => ['post'],
                ],
            ],
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['profile', 'account', 'networks', 'google-totp', 'api-key', 'disconnect', 'delete'],
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Shows profile settings form.
     *
     * @return string|Response
     */
    public function actionProfile()
    {
        /** @var Profile $model */
        $model = Yii::$container->get('ProfileQuery')->where(['user_id' => Yii::$app->user->id])->one();

        $this->performAjaxValidation($model);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', Yii::t('user', 'Your profile has been updated'));
            return $this->refresh();
        }

        return $this->render('profile', [
            'model' => $model,
        ]);
    }

    /**
     * Displays page where user can update account settings (username, email or password).
     *
     * @return string|Response
     */
    public function actionAccount()
    {
        $model = Yii::createObject([
            'class' => SettingsForm::class,
            'user' => Yii::$app->user->identity,
        ]);

        $this->performAjaxValidation($model);

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->update()) {
            Yii::$app->session->setFlash('success', Yii::t('user', 'Your account details have been updated'));
            return $this->refresh();
        }

        return $this->render('account', [
            'model' => $model,
        ]);
    }

    /**
     * Displays list of connected network accounts.
     *
     * @return string
     */
    public function actionNetworks()
    {
        return $this->render('networks');
    }

    /**
     * Displays Google two step auth form.
     *
     * @return string|Response
     */
    public function actionGoogleTotp()
    {
        $model = Yii::createObject([
            'class' => GoogleTotpForm::class,
            'user' => Yii::$app->user->identity,
        ]);

        $this->performAjaxValidation($model);

        if ($model->load(Yii::$app->request->post()) && $model->toggle()) {
            Yii::$app->user->switchIdentity(Yii::$app->user->identity, Yii::$app->user->enableAutoLogin ? $this->module->rememberFor : $this->module->loginLifetime);
            Yii::$app->session->setFlash('success', Yii::t('user', 'Your profile has been updated'));
            return $this->redirect($this->defaultAction);
        }

        return $this->render('google-totp', [
            'model' => $model
        ]);
    }

    /**
     * Change API key.
     *
     * @return string|Response
     */
    public function actionApiKey()
    {
        /** @var User $model */
        $model = Yii::$container->get('UserQuery')->byId(Yii::$app->user->id)->one();

        if (Yii::$app->request->isPost && !(Yii::$app->request->isAjax || Yii::$app->request->isPjax)) {
            $model->access_token = $model::generateAccessToken();
            $model->save(false, ['access_token']);
            Yii::$app->session->setFlash('success', Yii::t('user', 'Your API key has been updated'));
            return $this->refresh();
        }

        return $this->render('api-key', [
            'model' => $model
        ]);
    }

    /**
     * Disconnects a network account from user.
     *
     * @param int $id
     *
     * @return Response
     * @throws NotFoundHttpException
     * @throws ForbiddenHttpException
     */
    public function actionDisconnect(int $id)
    {
        $account = Yii::$container->get('AccountQuery')->byId($id)->one();
        if (!($account instanceof Account)) {
            throw new NotFoundHttpException();
        }
        if ($account->user_id != Yii::$app->user->id) {
            throw new ForbiddenHttpException();
        }
        $account->delete();

        return $this->redirect('networks');
    }

    /**
     * Completely deletes user's account.
     *
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionDelete()
    {
        if (!$this->module->enableAccountDelete) {
            throw new NotFoundHttpException(Yii::t('user', 'Not found'));
        }

        /** @var User $user */
        $user = Yii::$app->user->identity;
        Yii::$app->user->logout();
        $user->delete();

        Yii::$app->session->setFlash('info', Yii::t('user', 'Your account has been completely deleted'));
        return $this->goHome();
    }
}
