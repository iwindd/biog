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
            'deleteReleased' => true,
            'ttr' => 3600,
        ],
    ],
    'modules' => [
        'jodit' => 'yii2jodit\JoditModule',
    ],
];
