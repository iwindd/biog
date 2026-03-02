<?php
// ใน Docker ใช้ env (MYSQL_HOST, MYSQL_DATABASE, ...) ถ้าไม่มีใช้ค่าสำหรับรัน local
$dbHost = getenv('MYSQL_HOST') ?: 'localhost';
$dbName = getenv('MYSQL_DATABASE') ?: 'yii2advanced';
$dbUser = getenv('MYSQL_USER') ?: 'root';
$dbPass = getenv('MYSQL_PASSWORD') !== false ? getenv('MYSQL_PASSWORD') : 'verysecret';
// $dbPass = getenv('MYSQL_PASSWORD') !== false ? getenv('MYSQL_PASSWORD') : '?6}D3x#B';


return [
    'components' => [
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => "mysql:host={$dbHost};dbname={$dbName}",
            'username' => $dbUser,
            'password' => $dbPass,
            'charset' => 'utf8',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'viewPath' => '@common/mail',
        ],
    ],
];
