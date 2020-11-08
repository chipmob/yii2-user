<?php

namespace chipmob\user\components\traits;

use Yii;
use yii\base\Model;
use yii\bootstrap4\ActiveForm;
use yii\web\Response;

trait AjaxValidationTrait
{
    protected function performAjaxValidation(Model $model)
    {
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            Yii::$app->response->data = ActiveForm::validate($model);
            Yii::$app->response->send();
            Yii::$app->end();
        }
    }
}
