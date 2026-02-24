<?php

/* @var $this yii\web\View */

use frontend\components\FrontendHelper;
use common\components\FileLibrary;
use frontend\models\News;
use yii\helpers\Url;

$this->title = $news["title"];
$this->registerCss("nav {background-image: url('/images/banner/Blog_Banner.png'); }");
$this->registerCssFile(Url::base().'/js/gallery/jquery.fancybox.min.css', ['depends' => [\frontend\assets\AppAsset::className()]]);
$this->registerCssFile(Url::base().'/js/gallery/gallery.css', ['depends' => [\frontend\assets\AppAsset::className()]]);
$this->registerJsFile(Url::base().'/js/gallery/jquery.fancybox.min.js', ['depends' => [\frontend\assets\AppAsset::className()]]);
$this->registerJsFile(Url::base().'/js/gallery/gallery.js', ['depends' => [\frontend\assets\AppAsset::className()]]);

$linkMain = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST']; //
$_SESSION['currentUrl'] = $linkMain . $_SERVER['REQUEST_URI'];
$thisUrl = '/news/' . $news["id"];
//$linkMain = "https://biogang.devfunction.com";
$url = $linkMain . $thisUrl;

$curl = curl_init("http://developers.facebook.com/tools/debug/og/object?q=" . $url);
curl_setopt($curl, CURLOPT_HEADER, 0);

curl_exec($curl);
curl_close($curl);

$pathImage = $news["picture_path"];
echo FrontendHelper::getMetaImage("news", $pathImage);
echo FrontendHelper::getMetaTitle($this->title);
echo FrontendHelper::getDescription(strip_tags($news["description"]));
echo FrontendHelper::getUrl($url);


use frontend\models\Banner;
$banner = Banner::find()->where(['slug_url' => 'News'])->one();

?>
<?php $this->beginBlock('banner') ?>
<div class="section-banner">
    <?php if(!empty($banner->picture_path)): ?>
        <img src="/files/banner/<?php echo $banner->picture_path; ?>" class="banner ">
    <?php else: ?>
        <img src="/images/banner/News_Banner.png" class="banner ">
    <?php endif; ?>
</div>
<?php $this->endBlock() ?>

<div class="main-breadcrumb" aria-label="breadcrumb">
    <div class="container">
        <ol class="breadcrumb pl-0">
            <li class="breadcrumb-item home"><a href="/">Home</a></li>
            <li class="breadcrumb-item"><a href="/news">ข่าวและกิจกรรม</a></li>
            <li class="breadcrumb-item"><a href="/news/<?php echo $news["id"]; ?>"><?php echo $news["title"]; ?></a></li>
        </ol>
    </div>
</div>


<div class="site-news-detail" style="min-height: calc(100vh - 204px);">
    <div class="body-content">
        <section class="content-detail pt-4">
            <div class="container">
                <div class="row">
                    <div class="menu-sidebar">
                        <p class="menu"></p>
                        <?php echo $this->render('../layouts/sidebar'); ?>
                    </div>
                    <div class="section-main">
                        <div class="row">
                            <div class="col-lg-12">
                                <p class="menu text-left"><?php echo $news["title"]; ?></p>
                                <?php echo FileLibrary::getImageFrontend(News::UPLOAD_FOLDER_NEWS, $news["picture_path"]); ?>
                                <p class="description fr-view"><?php echo $news["description"]; ?></p>

                                <div class="image-galley">
                                    <div class="page-top">
                                        <div class="row">
                                            <?php foreach ($picture as $key => $value) {
                                                ?>
                                                    <div class="col-lg-4 col-md-4 col-6 thumb">
                                                        <a href="<?php echo FrontendHelper::blogImage($value['path'], 'news'); ?>"
                                                            class="fancybox" rel="ligthbox">
                                                            <img src="<?php echo FrontendHelper::blogImage($value['path'], 'news'); ?>"
                                                                class="zoom img-fluid " alt="">
                                                        </a>
                                                    </div>
                                            <?php
                                                }?>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                        <?php if(!empty($files)): ?>
                                            <div class="col-12 mb-1" >
                                                <p class="description ">เอกสารดาวน์โหลด:</p>
                                            </div>
                                        <?php endif; ?>
                                        <?php foreach ($files as $key => $value) {
                                           
                                        ?>
                                        <div class="col-12 mb-3" >
                                            <a download href="/files/news/<?=$value["path"]?>" target="_blank" class="btn btn-download">
                                                <i class="fas fa-download"></i> <?php echo $value["name"]; ?>
                                            </a>
                                        </div>
                                       
                                        <?php } ?>
                                    
                                    
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <?php if (!empty($other_news)) { ?>
            <section class="content-list">
                <div class="container">
                    <div class="row">
                        <div class="menu-sidebar"></div>
                        <div class="section-main">
                            <p class="menu text-left">ข่าวและกิจกรรมอื่นๆ</p>
                            <div class="row news-content">
                                <?php foreach ($other_news as $key => $value) { ?>
                                    <a href="/news/<?= $value["id"] ?>" class="col-lg-4">
                                        <div class="news-img">
                                            <?php echo FileLibrary::getImageFrontend(News::UPLOAD_FOLDER_NEWS, $value["picture_path"]); ?>
                                        </div>
                                        <h1 class="title"><?php echo $value["title"]; ?></h1>
                                        <p class="short-desc">
                                            <?php echo strip_tags($value["description"]); ?>
                                        </p>
                                        <p class="post">โพสต์ <?php echo FrontendHelper::getTime($value["created_at"]); ?>
                                            <span class="post-date">วันที่ <?php echo FrontendHelper::getDate($value["created_at"]) ?> </span>
                                            <span class="share"><i class="fas fa-share"></i> แชร์ </span>
                                            <span class="viewer"><i class="far fa-user"></i> ผู้เข้าชม <?= FrontendHelper::getPageView($value["id"], "news") ?> คน</span>
                                        </p>

                                    </a>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        <?php } ?>
    </div>
</div>