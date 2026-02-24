<?php

use yii\helpers\Html;
use frontend\assets\AppAsset;
use yii\widgets\Block;
use dmstr\cookieconsent\widgets\CookieConsent;

AppAsset::register($this);

?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" style="font-size: 16px;">

<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="keywords" content="bedo,biogang,ไบโอแก๊งค์,biodiversity,เยาวชน,ค่ายเยาวชน,อนุกรมวิธานน้อย, นักอนุกรมวิธานน้อย,ความหลากหลายทางชีวภาพ"/>     
    <meta name="description" content="“BIOGANG” เยาวชนรุ่นใหม่ ใส่ใจ ความหลากหลายทางชีวภาพ. .. สำนักงานพัฒนาเศรษฐกิจจากฐานชีวภาพ (องค์การมหาชน) มีนโยบายในการขับเคลื่อนองค์ความรู้ผ่านสื่อสร้างสรรค์" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <link rel="shortcut icon" href="/images/logo-favicon.png" type="image/vnd.microsoft.icon">
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500;1,600;1,700&display=swap" rel="stylesheet">
    
</head>
<?php 
    //$fontSize = "16px";
?>
<style>
    /* html{
        font-size: 16px;
    } */
   
</style>
<body>
    <?php $this->beginBody() ?>

    <?= CookieConsent::widget([
        'name' => 'cookie_consent_status',
        'path' => '/',
        'domain' => '',
        'expiryDays' => 365,
        'message' => Yii::t('cookie-consent', 'เว็บไซต์นี้มีการใช้คุ้กกี้ (Cookies) เพื่อเพิ่มประสบการณ์การใช้งาน และมีการเก็บข้อมูลตาม เงื่อนไขและนโยบายคุ้มครองข้อมูลส่วนบุคคล'),
        'save' => Yii::t('cookie-consent', 'Save'),
        'acceptAll' => Yii::t('cookie-consent', 'ตกลง'),
        'controlsOpen' => Yii::t('cookie-consent', 'Change'),
        'detailsOpen' => Yii::t('cookie-consent', 'Cookie Details'),
        'learnMore' => Yii::t('cookie-consent', ''),
        'visibleControls' => true,
        'visibleDetails' => false,
        'link' => '#',
        'consent' => [
            'necessary' => [
                'label' => Yii::t('cookie-consent', 'Necessary'),
                'checked' => true,
                'disabled' => true
            ],
            'statistics' => [
                'label' => Yii::t('cookie-consent', 'Statistics'),
                'cookies' => [
                    ['name' => '_ga'],
                    ['name' => '_gat', 'domain' => '', 'path' => '/'],
                    ['name' => '_gid', 'domain' => '', 'path' => '/']
                ],
                'details' => [
                    [
                        'title' => Yii::t('cookie-consent', 'Google Analytics'),
                        'description' => Yii::t('cookie-consent', 'Create statistics data')

                    ],
                    [
                        'title' => Yii::t('cookie-consent', 'Goal'),
                        'description' => Yii::t('cookie-consent', '_ga, _gat, _gid, _gali')

                    ]
                ]
            ]
        ]
    ]) ?>

    <?php
    echo $this->render('navbar');
    echo $this->render('header');
    ?>
    
    <main>
        <?= $content; ?>
    </main>

    <?php
    echo $this->render('footer');
    ?>

    <script src="https://cdn.tiny.cloud/1/6w6xoj5iltwa58ublz29tm8mqy13v1sgq0ahb4wk4rf2z083/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>

    <script>
        tinymce.init({
        selector: '#mytextarea'
        });
    </script>

    <?php if (\Yii::$app->cookieConsentHelper->hasConsent('statistics')): ?>
        <!-- Global site tag (gtag.js) - Google Analytics -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=UA-178884145-1"></script>
        <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'UA-178884145-1');
        </script>

    <?php endif; ?>

    <?php $this->endBody() ?>

<!-- Cookie Consent by https://www.cookiewow.com -->

    <script type="text/javascript" src="https://cookiecdn.com/cwc.js"></script>

    <script id="cookieWow" type="text/javascript" src="https://cookiecdn.com/configs/XbavodYrbVCGwBVjgaGWFZpi" data-cwcid="XbavodYrbVCGwBVjgaGWFZpi"></script>

</body>

</html>
<?php $this->endPage() ?>
