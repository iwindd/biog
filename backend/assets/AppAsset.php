<?php

namespace backend\assets;

use yii\web\AssetBundle;

/**
 * Main backend application asset bundle.
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/site.min.css?v=1',
        'css/jquery-ui.css',
        'summernote/summernote.css',
    ];
    public $js = [
        'js/jquery-ui.js',
        'js/youtube-iframe.min.js?v=1',
        'summernote/summernote.js',
        'js/summernote-form.js',
        //'js/map.min.js?v=1',
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
}
