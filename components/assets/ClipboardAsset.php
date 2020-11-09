<?php

namespace chipmob\user\components\assets;

use yii\web\AssetBundle;

class ClipboardAsset extends AssetBundle
{
    public $sourcePath = '@npm/clipboard/dist';
    public $js = [
        YII_DEBUG ? 'clipboard.js' : 'clipboard.min.js',
    ];
}
