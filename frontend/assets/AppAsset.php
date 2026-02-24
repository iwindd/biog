<?php

namespace frontend\assets;

use yii\web\AssetBundle;

/**
 * Main frontend application asset bundle.
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'https://fonts.googleapis.com/css2?family=Kanit:wght@400;500;600;700&display=swap',
        'css/style.min.css',
        'fontawesome/css/all.css',
        'sweetalert2/dist/sweetalert2.min.css',
        //'css/froala_style.min.css',
        'summernote/summernote-bs4.css',
    ];
    public $js = [
        'js/svg-inject.js',
        'js/customsvg.js',
        'js/custom.min.js',
        'sweetalert2/dist/sweetalert2.all.min.js',
        'summernote/summernote-bs4.js',
        'js/summernote-form.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap4\BootstrapAsset',
        
    ];
}
