<?php

/* @var $this yii\web\View */
/* @var $name string */
/* @var $message string */
/* @var $exception Exception */

use yii\helpers\Html;

// print "<pre>";
// print_r($name);
// print "</pre>";
// exit();

$this->title = $name;
?>
<div class="site-error">

    <!-- <div class="section-banner">
        <img src="/images/banner/Interactive Map_Banner.svg" class="banner">
    </div> -->

    <div id="container" class="error-page ">
        <div class="overlay"></div>
        <div class="item-title text-center" style="margin-top: 12rem;">
            <div class="error-message">
                <p style="font-size: 3rem;">ERROR <?= Html::encode($this->title) ?></p>
                <p style="font-size: 1.5rem;"><?= nl2br(Html::encode($message)) ?></p>
            </div>
            <div class="link-bottom " style="margin-bottom: 12rem;"> 
                <a class="btn btn-style-site btn-view-all" href="/"><i class="fas fa-home"></i></i> กลับหน้าแรก</a> 
            </div>
        </div>
    </div>

    <!-- <h1><?= Html::encode($this->title) ?></h1>

    <div class="alert alert-danger">
        <?= nl2br(Html::encode($message)) ?>
    </div>

    <p>
        The above error occurred while the Web server was processing your request.
    </p>
    <p>
        Please contact us if you think this is a server error. Thank you.
    </p> -->

</div>