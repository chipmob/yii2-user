<?php

namespace chipmob\user\components\traits;

use chipmob\user\Module;
use Exception;
use Yii;

/**
 * Trait ModuleTrait
 *
 * @property-read Module $module
 */
trait ModuleTrait
{
    private ?Module $_module = null;

    public function getModule(): ?Module
    {
        if (empty($this->_module)) {
            $this->_module = Yii::$app->getModule('user');
        }

        if (empty($this->_module)) {
            throw new Exception("Module yii2-user not found, may be you didn't add it to your config?");
        }

        return $this->_module;
    }
}
