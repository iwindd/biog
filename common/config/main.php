<?php
return [
    'bootstrap' => ['queue'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'mutex' => [
            'class' => 'yii\mutex\MysqlMutex',
        ],
        'queue' => [
            'class' => \yii\queue\db\Queue::class,
            'db' => 'db',
            'tableName' => '{{%queue}}',
            'channel' => 'default',
            'deleteReleased' => false, // Changed to false to allow retry on failure
            'ttr' => 7200, // Increased to 2 hours for large exports
            'attempts' => 3, // Allow up to 3 attempts before giving up
        ],
    ],
    'modules' => [
        'jodit' => 'yii2jodit\JoditModule',
    ],
];
