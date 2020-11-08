<?php

namespace chipmob\user\components\assets;

use yii\web\AssetBundle;

class ClipboardAsset extends AssetBundle
{
    public $sourcePath = '@npm/clipboard/dist';
    public $js = [
        'clipboard.min.js',
    ];
}
