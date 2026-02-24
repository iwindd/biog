<?php
use \yii\web\Request;
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);
$baseUrl = str_replace('/backend/web', '', (new Request)->getBaseUrl()); 
return [
    'id' => 'app-backend',
    'name'=>'สำนักงานพัฒนาเศรษฐกิจจากฐานชีวภาพ (องค์การมหาชน)',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'backend\controllers',
    'timeZone' => 'Asia/Bangkok',
    'bootstrap' => ['log'],
    'modules' => [


        'user' => [
            'class' => 'dektrium\user\Module',
            'mailer'=>['viewPath'=>'@frontend/views/usermail',],
            'enableFlashMessages' => false,
            'enableConfirmation' => false,
            'enableUnconfirmedLogin' => true,
            'confirmWithin' => 21600,
            'cost' => 12,
            'admins' => ['admin'],
            'controllerMap' => [
                'security' => [
                    'class' => \dektrium\user\controllers\SecurityController::className(),
                    'on ' . \dektrium\user\controllers\SecurityController::EVENT_BEFORE_LOGIN => function ($e) {
                        Yii::$app->response->redirect(array('/login'))->send();
                        Yii::$app->end();
                    },
                ],

            ],
        ],

        'jodit' => [
            'class' => 'yii2jodit\JoditModule',
            'extensions'=>['jpg','png','gif', 'pdf', 'doc', 'docx', 'mp3'],
            'root'=> '@frontend/web/uploads/',
            'baseurl'=> 'https://biogang.devfunction.com/uploads/',
            'maxFileSize'=> '2mb',
            'defaultPermission'=> 0775,
        ],
        //...
    ],
    'components' => [

        'i18n'=>[
            'translations'=>[
                'app' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@common/messages',
                    'fileMap' => [
                        'app' => 'app.php',
                    ],
                ],
            ],
        ],

        'formatter' => [
            'class' => 'yii\i18n\Formatter',
            'nullDisplay' => '',
        ],

        'request' => [
            'csrfParam' => '_csrf-backend',
            'baseUrl' => '/admin'
        ],
        'view' => [
            'theme' => [
                // 'pathMap' => [
                //     '@app/views' => '@vendor/dmstr/yii2-adminlte-asset/example-views/yiisoft/yii2-app'
                // ],
                'pathMap' => [
                    '@dektrium/user/views' => '@backend/views/user',
                    '@backend/views' => '@backend/themes/adminlte',
                    
                ],
            ],
        ],
        'user' => [
            //'identityClass' => 'app\models\User',
            'identityClass' => 'dektrium\user\models\User',
            'enableAutoLogin' => true,
        ],
        'session' => [
            // this is the name of the session cookie used for login on the backend
            'name' => 'BiogBackend',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => '/error/index',
        ],
        'urlManager' => [
            'class' => 'yii\web\UrlManager',
            // Disable index.php
            'showScriptName' => false,
            // Disable r= routes
            'enablePrettyUrl' => true,
            'rules' => [
                    '/login' => 'site/login',
                    '/contact-us/<id:\d+>' => 'contact-us/view',

                    '/school/teacher-student/<id:\d+>' => 'school/teacher-student',

                    'readfile/preview/<code:\w+>' => 'readfile/preview',
                    'readfile/download-knowledge/<code:\w+>' => 'readfile/download-knowledge',
                    'readfile/download-news/<code:\w+>' => 'readfile/download-news',
                    'readfile/download-blog/<code:\w+>' => 'readfile/download-blog',

                    '/banner-news' => 'banner/news',
                    '/banner-knowledge' => 'banner/knowledge',

                    '/content-banner/<id:\d+>' => 'content-banner/view',

                    '/approved-teacher/<id:\d+>' => 'approved-teacher/view',
                    '/approved-teacher/update/<id:\d+>' => 'approved-teacher/update',
                    '/approved-teacher/delete/<id:\d+>' => 'approved-teacher/delete',

                    '/content-animal/<id:\d+>' => 'content-animal/view',
                    '/content-animal/update/<id:\d+>' => 'content-animal/update',
                    '/content-animal/delete/<id:\d+>' => 'content-animal/delete',

                    '/content-plant/<id:\d+>' => 'content-plant/view',
                    '/content-plant/update/<id:\d+>' => 'content-plant/update',
                    '/content-plant/delete/<id:\d+>' => 'content-plant/delete',

                    '/content-fungi/<id:\d+>' => 'content-fungi/view',
                    '/content-fungi/update/<id:\d+>' => 'content-fungi/update',
                    '/content-fungi/delete/<id:\d+>' => 'content-fungi/delete',

                    '/content-expert/<id:\d+>' => 'content-expert/view',
                    '/content-expert/update/<id:\d+>' => 'content-expert/update',
                    '/content-expert/delete/<id:\d+>' => 'content-expert/delete',

                    '/content-ecotourism/<id:\d+>' => 'content-ecotourism/view',
                    '/content-ecotourism/update/<id:\d+>' => 'content-ecotourism/update',
                    '/content-ecotourism/delete/<id:\d+>' => 'content-ecotourism/delete',

                    '/content-product/<id:\d+>' => 'content-product/view',
                    '/content-product/update/<id:\d+>' => 'content-product/update',
                    '/content-product/delete/<id:\d+>' => 'content-product/delete',

                    '/product-category/<id:\d+>' => 'product-category/view',
                    '/product-category/update/<id:\d+>' => 'product-category/update',
                    '/product-category/delete/<id:\d+>' => 'product-category/delete',

                    '/expert-category/<id:\d+>' => 'expert-category/view',
                    '/expert-category/update/<id:\d+>' => 'expert-category/update',
                    '/expert-category/delete/<id:\d+>' => 'expert-category/delete',

                    '/contact-us/update/<id:\d+>' => 'contact-us/update',
                    '<controller:\w+>/<id:\d+>' => '<controller>/view',
                    '<controller:\w+>/<action:\w+>/<id:\d+>' => '<controller>/<action>',
                    '<controller:\w+>/<action:\w+>' => '<controller>/<action>',
                    ['class' => 'yii\rest\UrlRule', 'controller' => 'location', 'except' => ['delete','GET', 'HEAD','POST','OPTIONS'], 'pluralize'=>false],
                    '<module:\w+>/<controller:\w+>/<action:\w+>' => '<module>/<controller>/<action>',
            ],
        ],

        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
                 'useFileTransport' => false,
                 'transport' => [
                     'class' => 'Swift_SmtpTransport',
                     'host' => 'smtp.gmail.com',
                     'username' => 'biogang.smtp@gmail.com',
                     'password' => 'nsrxdrammdozafgg',
                     'port' => '587',
                     'encryption' => 'tls',
                 ],
         ],

    ],
    'params' => $params,
];
