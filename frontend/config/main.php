<?php
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id' => 'app-frontend',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'frontend\controllers',
    'timeZone' => 'Asia/Bangkok',
    'components' => [
        'request' => [
            'csrfParam' => '_csrf-frontend',
            'baseUrl' => '',
        ],
        'user' => [
            // 'identityClass' => 'app\models\User',
            'identityClass' => 'dektrium\user\models\User',
            'enableAutoLogin' => true,
        ],
        'cookieConsentHelper' => [
            'class' => dmstr\cookieconsent\components\CookieConsentHelper::class
        ],
        'session' => [
            // this is the name of the session cookie used for login on the frontend
            'name' => 'Yii2-Diversition-Template',
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
            'errorAction' => 'site/error',
        ],
        'urlManager' => [
            'class' => 'yii\web\UrlManager',
            'showScriptName' => false,
            'enablePrettyUrl' => true,
            'rules' => [
                '/profile' => '/user/profile',
                '/login' => '/login/index',
                '/register' => '/user/registration/register',
                '/forget-password' => '/resetpassword/index',
                '/member/recover/<id:\d+>/<code:[^/]+>' => 'resetpassword/reset',
                '/logout' => '/site/logout',
                // '/site/login' => '/user/login',
                '/about' => 'site/about',
                '/contact' => 'site/contact',
                '/terms-conditions' => 'site/privacy',
                '/data-protection-policy' => 'site/protection',
                '/interactive-map' => 'map/index',
                '/content-plant/<id:\d+>' => 'content-plant/view',
                '/content-animals/<id:\d+>' => 'content-animals/view',
                '/content-ecotourism/<id:\d+>' => 'content-ecotourism/view',
                '/content-expert/<id:\d+>' => 'content-expert/view',
                '/content-fungi/<id:\d+>' => 'content-fungi/view',
                '/content-product/<id:\d+>' => 'content-product/view',
                '/content/<controller:\w+>/<action:\w+>/<id:\d+>' => 'content/<controller>/<action>',
                'content/approve-content/<id:\d+>' => 'content/<controller>/<action>',
                'content/delete/<id:\d+>' => 'content/approve/delete',
                '/api/biogang-types' => 'api/biogang/type',
                '/api/biogang-items/plants' => 'api/biogang/plants',
                '/api/biogang-items/animals' => 'api/biogang/animals',
                '/api/biogang-items/micros' => 'api/biogang/micros',
                '/api/biogang-data' => 'api/biogang/data',
                '/api/word-cloud-count' => 'api/biogang/wordcloud-count',
                '/api/keyword-map-count' => 'api/biogang/keywordmap-count',
                '/biogang/plant' => 'api/biogang/get-plant',
                '/biogang/animal' => 'api/biogang/get-animal',
                '/biogang/micros' => 'api/biogang/get-micros',
                '/biogang/group-list/<type:\w+>/<id:\d+>' => 'api/biogang/group-list',
                '<controller:\w+>/<id:\d+>' => '<controller>/view',
                '<controller:\w+>/<action:\w+>/<id:\d+>' => '<controller>/<action>',
                '<controller:\w+>/<action:\w+>' => '<controller>/<action>',
                // [
                //     'class' => 'yii\rest\UrlRule',
                //     'controller' => 'location',
                //     'except' => [
                //         'delete', 'GET', 'HEAD', 'POST', 'OPTIONS'
                //     ],
                //     'pluralize' => false
                // ],
                '<module:\w+>/<controller:\w+>/<action:\w+>' => '<module>/<controller>/<action>',
            ],
        ],
        'reCaptcha' => [
            'class' => 'himiklab\yii2\recaptcha\ReCaptchaConfig',
            'siteKeyV2' => '6Lcfv74ZAAAAABHCFqZBwgdU4dVi2klEiXvSY-Cj',
            'secretV2' => '6Lcfv74ZAAAAABLXfpba-wAPdhdynSeEKy-sEh8j',
            'siteKeyV3' => 'your siteKey v3',
            'secretV3' => 'your secret key v3',
        ],
        'view' => [
            'theme' => [
                'pathMap' => [
                    '@dektrium/user/views/registration' => '@frontend/views/user',
                    '@dektrium/user/views/profile' => '@frontend/views/user'
                ],
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
        'i18n' => [
            'translations' => [
                'cookie-consent' => [
                    'class' => \yii\i18n\PhpMessageSource::class,
                    'basePath' => '@app/messages',  // base path of your message file
                    'sourceLanguage' => 'th'
                ]
            ]
        ],
        // meta data for SEO
        // 'meta' => [
        //     'class' => 'frontend\components\MetaComponent',
        // ],
    ],
    'modules' => [
        'user' => [
            'class' => 'dektrium\user\Module',
            'mailer' => [
                'viewPath' => '@frontend/views/usermail',
            ],
            'enableFlashMessages' => false,
            'enableConfirmation' => false,
            'enableUnconfirmedLogin' => true,
            'confirmWithin' => 21600,
            'cost' => 12,
            'admins' => ['admin'],
            'controllerMap' => [
                'registration' => 'frontend\controllers\user\RegisterController',
                'profile' => 'frontend\controllers\user\ProfileController',
                'security' => [
                    'class' => \dektrium\user\controllers\SecurityController::className(),
                    'on ' . \dektrium\user\controllers\SecurityController::EVENT_BEFORE_LOGIN => function ($e) {
                        Yii::$app->response->redirect(array('/login'))->send();
                        Yii::$app->end();
                    },
                ],
                'recovery' => 'frontend\controllers\user\ResetPasswordController',
                // 'recovery'=>[
                //     'class' => \dektrium\user\controllers\RecoveryController::className(),
                //       'on ' . \dektrium\user\controllers\RecoveryController::EVENT_AFTER_RESET => function ($e) {
                //           \Yii::$app->getSession()->setFlash('alert-register', [
                //                   'body'=>'You has been change password successfully. ',
                //                   'options'=>['class'=>'alert-success-reset']
                //                 ]);
                //           Yii::$app->response->redirect(array('/login'))->send();
                //           Yii::$app->end();
                //       },
                // ],
            ],
        ],
        'jodit' => [
            'class' => 'yii2jodit\JoditModule',
            'extensions' => ['jpg', 'png', 'gif'],
            'root' => '@webroot/uploads/',
            'baseurl' => '/uploads/',
            'maxFileSize' => '20mb',
            'defaultPermission' => 0775,
        ],
        // ...
    ],
    'params' => $params,
];
