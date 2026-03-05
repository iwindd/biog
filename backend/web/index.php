<?php
$isDebug = filter_var(getenv('YII_DEBUG'), FILTER_VALIDATE_BOOLEAN);
$env = getenv('YII_ENV') ?: 'prod';

defined('YII_DEBUG') || define('YII_DEBUG', $isDebug);
defined('YII_ENV') || define('YII_ENV', $env);

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../../vendor/yiisoft/yii2/Yii.php';
require __DIR__ . '/../../common/config/bootstrap.php';
require __DIR__ . '/../config/bootstrap.php';

$config = yii\helpers\ArrayHelper::merge(
    require __DIR__ . '/../../common/config/main.php',
    require __DIR__ . '/../../common/config/main-local.php',
    require __DIR__ . '/../config/main.php',
    require __DIR__ . '/../config/main-local.php'
);

if ($isDebug) {
    require __DIR__ . '/../../common/debugging.php';
}

(new yii\web\Application($config))->run();
