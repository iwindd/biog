<?php

use yii\helpers\Html;
use frontend\assets\AppAsset;

AppAsset::register($this);
// $globalParams = $this->params['globalParams'];
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">

<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet">

</head>

<body>
    <?php $this->beginBody() ?>

    <?php
    // echo $this->render('header', [
    //     'background' => $globalParams->background
    // ]);
    ?>

    <?php
    // echo $this->render('mainMenu', [
    //     'background' => $globalParams->background
    // ]);
    ?>

    <main>
        <?= $content; ?>
    </main>

    <?php
    echo $this->render('../footer');
    ?>

    <?php $this->endBody() ?>
</body>

</html>
<?php $this->endPage() ?>