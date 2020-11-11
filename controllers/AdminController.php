<?php

namespace chipmob\user\controllers;

use chipmob\user\components\helpers\Password;
use chipmob\user\components\traits\AjaxValidationTrait;
use chipmob\user\components\traits\ModuleTrait;
use chipmob\user\models\search\ActionSearch;
use chipmob\user\models\search\LogSearch;
use chipmob\user\models\search\UserSearch;
use chipmob\user\models\User;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * AdminController allows you to administrate users.
 */
class AdminController extends Controller
{
    use ModuleTrait;
    use AjaxValidationTrait;

    /** @inheritdoc */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'switch' => ['post'],
                    'confirm' => ['post'],
                    'block' => ['post'],
                    'resend-password' => ['post'],
                    'remove' => ['post'],
                    'delete' => ['post'],
                ],
            ],
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /** @inheritdoc */
    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {
            if (in_array($action->id, ['index', 'update', 'update-profile'])) {
                Url::remember();
            }
            return true;
        }
        return false;
    }

    /**
     * Lists all User models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = Yii::createObject(UserSearch::class);
        $dataProvider = $searchModel->search(Yii::$app->request->get());

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }

    /**
     * Creates a new User model.
     * If creation is successful, the browser will be redirected to the 'index' page.
     *
     * @return string|Response
     */
    public function actionCreate()
    {
        $user = Yii::createObject([
            'class' => User::class,
            'scenario' => User::SCENARIO_CREATE,
        ]);

        $this->performAjaxValidation($user);

        if ($user->load(Yii::$app->request->post()) && $user->create()) {
            Yii::$app->session->setFlash('success', Yii::t('user', 'User has been created'));
            return $this->redirect(['update', 'id' => $user->id]);
        }

        return $this->render('create', [
            'user' => $user,
        ]);
    }

    /**
     * Updates an existing User model.
     *
     * @param int $id
     *
     * @return string|Response
     * @throws NotFoundHttpException
     */
    public function actionUpdate(int $id)
    {
        $user = $this->findModel($id);
        $user->setScenario(User::SCENARIO_UPDATE);

        $this->performAjaxValidation($user);

        if ($user->load(Yii::$app->request->post()) && $user->save()) {
            Yii::$app->session->setFlash('success', Yii::t('user', 'Account details have been updated'));
            return $this->refresh();
        }

        return $this->render('_account', [
            'user' => $user,
        ]);
    }

    /**
     * Updates an existing profile.
     *
     * @param int $id
     *
     * @return string|Response
     * @throws NotFoundHttpException
     */
    public function actionUpdateProfile(int $id)
    {
        $user = $this->findModel($id);
        $profile = $user->profile;

        $this->performAjaxValidation($profile);

        if ($profile->load(Yii::$app->request->post()) && $profile->save()) {
            Yii::$app->session->setFlash('success', Yii::t('user', 'Profile details have been updated'));
            return $this->refresh();
        }

        return $this->render('_profile', [
            'user' => $user,
            'profile' => $profile,
        ]);
    }

    /**
     * Switches to the given user for the rest of the Session.
     * When no id is given, we switch back to the original admin user
     * that started the impersonation.
     *
     * @param int|null $id
     *
     * @return Response
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionSwitch(?int $id = null)
    {
        if (!$this->module->enableImpersonateUser) {
            throw new ForbiddenHttpException(Yii::t('user', 'Impersonate user is disabled in the application configuration'));
        }

        if (empty($id) && Yii::$app->session->has(User::ORIGINAL_USER_SESSION_KEY)) {
            $user = $this->findModel((int)Yii::$app->session->get(User::ORIGINAL_USER_SESSION_KEY));
            Yii::$app->session->remove(User::ORIGINAL_USER_SESSION_KEY);
        } else {
            if (!Yii::$app->user->identity->isAdmin) {
                throw new ForbiddenHttpException;
            }
            $user = $this->findModel($id);
            if ($user->isBlocked || $user->isRemoved) {
                throw new ForbiddenHttpException;
            }
            Yii::$app->session->set(User::ORIGINAL_USER_SESSION_KEY, Yii::$app->user->id);
        }

        Yii::$app->user->switchIdentity($user, $this->module->switchUserLifetime);
        $user->trigger(User::EVENT_IMPERSONATE);

        return $this->goHome();
    }

    /**
     * Confirms the User.
     *
     * @param int $id
     *
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionConfirm(int $id)
    {
        $model = $this->findModel($id);
        $model->confirm();
        Yii::$app->session->setFlash('success', Yii::t('user', 'User has been confirmed'));
        return $this->goBack();
    }

    /**
     * Blocks the user.
     *
     * @param int $id
     *
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionBlock(int $id)
    {
        if ($id == Yii::$app->user->id) {
            Yii::$app->session->setFlash('danger', Yii::t('user', 'You can not block your own account'));
        } else {
            $user = $this->findModel($id);
            if ($user->isBlocked) {
                $user->unblock();
                Yii::$app->session->setFlash('success', Yii::t('user', 'User has been unblocked'));
            } else {
                $user->block();
                Yii::$app->session->setFlash('success', Yii::t('user', 'User has been blocked'));
            }
        }
        return $this->goBack();
    }

    /**
     * Generates a new password and sends it to the user.
     *
     * @param int $id
     *
     * @return Response
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionResendPassword(int $id)
    {
        $user = $this->findModel($id);
        if ($user->isAdmin) {
            throw new ForbiddenHttpException(Yii::t('user', 'Password generation is not possible for admin users'));
        }

        $user->password = Password::generate(8);
        if ($user->resendPassword()) {
            Yii::$app->session->setFlash('success', Yii::t('user', 'New Password has been generated and sent to user'));
        } else {
            Yii::$app->session->setFlash('danger', Yii::t('user', 'Error while trying to generate new password'));
        }

        return $this->goBack();
    }

    /**
     * Remove the user.
     *
     * @param int $id
     *
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionRemove(int $id)
    {
        if ($id == Yii::$app->user->id) {
            Yii::$app->session->setFlash('danger', Yii::t('user', 'You can not remove your own account'));
        } else {
            $user = $this->findModel($id);
            if ($user->isRemoved) {
                $user->restore();
                Yii::$app->session->setFlash('success', Yii::t('user', 'User has been restored'));
            } else {
                $user->remove();
                Yii::$app->session->setFlash('success', Yii::t('user', 'User has been removed'));
            }
        }
        return $this->goBack();
    }

    /**
     * Deletes an existing User model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     *
     * @param int $id
     *
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionDelete(int $id)
    {
        if ($id == Yii::$app->user->id) {
            Yii::$app->session->setFlash('danger', Yii::t('user', 'You can not remove your own account'));
        } else {
            $model = $this->findModel($id);
            $model->delete();
            Yii::$app->session->setFlash('success', Yii::t('user', 'User has been deleted'));
        }
        return $this->redirect($this->defaultAction);
    }

    /**
     * View User auth actions
     *
     * @param $id
     *
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionAction(int $id)
    {
        $user = $this->findModel($id);

        $searchModel = Yii::createObject([
            'class' => ActionSearch::class,
            'user' => $user,
        ]);
        $dataProvider = $searchModel->search(Yii::$app->request->get());

        return $this->render('action', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }

    /**
     * View User log actions
     *
     * @param $id
     *
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionLog(int $id)
    {
        $user = $this->findModel($id);

        $searchModel = Yii::createObject([
            'class' => LogSearch::class,
            'user' => $user,
        ]);
        $dataProvider = $searchModel->search(Yii::$app->request->get());

        return $this->render('log', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }

    /**
     * Finds the User model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param int $id
     *
     * @return User
     * @throws NotFoundHttpException
     */
    protected function findModel(int $id): User
    {
        $user = Yii::$container->get('UserQuery')->byId($id)->one();
        if (!($user instanceof User)) {
            throw new NotFoundHttpException('The requested page does not exist');
        }
        return $user;
    }
}
