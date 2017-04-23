<?php

namespace r0n1k\yii2imagewidget;

class ImageWidgetAsset extends \yii\web\AssetBundle
{

    public $js = [
        'bootstrap.file-input.js',
        'bundle.js',
    ];

    public $css = [
        'bundle.css',
    ];

    public function init()
    {
        $this->sourcePath = dirname(__FILE__).'/assets';
    }

    public $depends = [
        'yii\web\JqueryAsset',
    ];
}
