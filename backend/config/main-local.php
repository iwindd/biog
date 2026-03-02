<?php
$config = [
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'V4JXNHvdjK6cOi_OZMHOl5mmKODP1KGt',
        ],
    ],
];

if (!YII_ENV_TEST) {
    // เปิดใช้งาน Debug Module
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // หากต้องการให้เครื่องอื่นสามารถดึงหน้า Debug มาดูได้ ให้เอาคอมเมนต์บรรทัดล่างออก แล้วใส่ IP หรือ '*' (ไม่แนะนำให้เปิด '*' บน Production)
        'allowedIPs' => ['127.0.0.1', '::1', '*'],
    ];
    // แนะนำให้เปิด Gii (Code Generator) ควบคู่ไปด้วย
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        'allowedIPs' => ['127.0.0.1', '::1', '*'],
    ];
}

return $config;