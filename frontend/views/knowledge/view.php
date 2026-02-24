<?php

/* @var $this yii\web\View */

use frontend\components\FrontendHelper;
use common\components\FileLibrary;
use frontend\models\Knowledge;
use yii\helpers\Url;

$this->title = $knowledge["title"];
$this->registerCss("nav {background-image: url('/images/banner/Blog_Banner.png'); }");
$this->registerCssFile(Url::base().'/js/gallery/jquery.fancybox.min.css', ['depends' => [\frontend\assets\AppAsset::className()]]);
$this->registerCssFile(Url::base().'/js/gallery/gallery.css', ['depends' => [\frontend\assets\AppAsset::className()]]);
$this->registerJsFile(Url::base().'/js/gallery/jquery.fancybox.min.js', ['depends' => [\frontend\assets\AppAsset::className()]]);
$this->registerJsFile(Url::base().'/js/gallery/gallery.js', ['depends' => [\frontend\assets\AppAsset::className()]]);

$linkMain = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST']; //
$_SESSION['currentUrl'] = $linkMain . $_SERVER['REQUEST_URI'];
$thisUrl = '/knowledge/' . $knowledge["id"];
$url = $linkMain . $thisUrl;

$curl = curl_init("http://developers.facebook.com/tools/debug/og/object?q=" . $url);
curl_setopt($curl, CURLOPT_HEADER, 0);

curl_exec($curl);
curl_close($curl);

$pathImage = $knowledge["picture_path"];
echo FrontendHelper::getMetaImage("knowledge", $pathImage);
echo FrontendHelper::getMetaTitle($this->title);
echo FrontendHelper::getDescription(strip_tags($knowledge["description"]));
echo FrontendHelper::getUrl($url);

use frontend\models\Banner;
$banner = Banner::find()->where(['slug_url' => 'Knowledge'])->one();
?>
<?php $this->beginBlock('banner') ?>
<div class="section-banner">
    <?php if(!empty($banner->picture_path)): ?>
        <img src="/files/banner/<?php echo $banner->picture_path; ?>" class="banner ">
    <?php else: ?>
        <img src="/images/banner/Knowledge_Banner.png" class="banner ">
    <?php endif; ?>
</div>
<?php $this->endBlock() ?>

<div class="main-breadcrumb" aria-label="breadcrumb">
    <div class="container">
        <ol class="breadcrumb pl-0">
            <li class="breadcrumb-item home"><a href="/">Home</a></li>
            <li class="breadcrumb-item"><a href="/knowledge">องค์ความรู้ออนไลน์</a></li>
            <li class="breadcrumb-item"><a href="/knowledge/<?php echo $knowledge["id"]; ?>"><?php echo $knowledge["title"]; ?></a></li>
        </ol>
    </div>
</div>


<div class="site-knowledge-detail" style="min-height: calc(100vh - 204px);">
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
                            <div class="col-lg-12 fr-view">
                                <p class="menu text-left"><?php echo $knowledge["title"]; ?></p>
                                <?php echo FileLibrary::getImageFrontend(Knowledge::UPLOAD_FOLDER_KNOWLEDGE, $knowledge["picture_path"]); ?>
                                <p class="description"><?php echo $knowledge["description"]; ?></p>
                                
                            </div>
                            <div class="col-lg-12 mb-5">
                                <div class=" justify-content-center ">
                                    <?php if(!empty($knowledge['path']) && strpos($knowledge['path'], 'youtube') !== false): ?>
                                        
                                        <iframe width="425" height="350" src="<?php echo FrontendHelper::getYoutubeEmbedUrl($knowledge['path']); ?>"></iframe>

                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-lg-12">                
                                <div class="image-galley">
                                    <div class="page-top">
                                        <div class="row">
                                            <?php foreach ($picture as $key => $value) {
                                                ?>
                                                    <div class="col-lg-4 col-md-4 col-6 thumb">
                                                        <a href="<?php echo FrontendHelper::blogImage($value['path'], 'knowledge'); ?>"
                                                            class="fancybox" rel="ligthbox">
                                                            <img src="<?php echo FrontendHelper::blogImage($value['path'], 'knowledge'); ?>"
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
                                            <a download href="/files/knowledge/<?=$value["path"]?>" target="_blank" class="btn btn-download">
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
        <?php if (!empty($other_knowledge)) { ?>
            <section class="content-list">
                <div class="container">
                    <div class="row">
                        <div class="menu-sidebar"></div>
                        <div class="section-main">
                            <p class="menu text-left">องค์ความรู้ออนไลน์อื่นๆ</p>
                            <div class="row">
                                <?php foreach ($other_knowledge as $key => $value) {
                                    $thisUrl = '/knowledge/' . $value["id"];
                                ?>
                                    <a href="/knowledge/<?= $value["id"] ?>" class="col-lg-4 mb-3">
                                        <object>
                                            
                                                <?php if($value['type'] == 'Video'): ?>
                                                    <div class="knowledge-img knowledge-video">
                                                    <?php if(!empty($value['path']) && strpos($value['path'], 'youtube') !== false): ?>
                                                        <iframe  height="250" src="<?php echo FrontendHelper::getYoutubeEmbedUrl($value['path']); ?>"></iframe>
                                                    <?php else: ?>
                                                        <?php echo FileLibrary::getImageFrontend(Knowledge::UPLOAD_FOLDER_KNOWLEDGE, $value["picture_path"]); ?>
                                                    <?php endif; ?>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="knowledge-img">
                                                        <?php echo FileLibrary::getImageFrontend(Knowledge::UPLOAD_FOLDER_KNOWLEDGE, $value["picture_path"]); ?>
                                                    </div>
                                                <?php endif; ?>
                                            
                                            <h1 class="title"><?php echo $value["title"]; ?></h1>
                                            <p class="short-desc">
                                                <?php echo strip_tags($value["description"]); ?>
                                            </p>
                                            <div class="post">โพสต์ <?php echo FrontendHelper::getTime($value["created_at"]); ?>
                                                <span class="post-date">วันที่ <?php echo FrontendHelper::getDate($value["created_at"]) ?> </span>
                                                <span class="dropdown share">
                                                    <span class="share-label"><i class="fas fa-share"></i> แชร์</span>
                                                    <div class="dropdown-content share-dropdown-item">
                                                        <a class="fb-share" data-href="<?php echo $linkMain . $thisUrl; ?>" target="_blank" href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $linkMain . $thisUrl; ?>&amp;src=sdkpreparse&display=popup"><i class="fab fa-facebook-square"></i> Facebook
                                                        </a>
                                                        <a class="line-share" data-href="<?php echo $linkMain . $thisUrl; ?>" href="https://lineit.line.me/share/ui?url=<?php echo $linkMain . $thisUrl; ?>" target="_blank" data-url="<?php echo $linkMain . $thisUrl; ?>"><i class="fab fa-line"></i> Line</a>
                                                    </div>
                                                </span>
                                                <span class="viewer"><i class="far fa-user"></i> ผู้เข้าชม <?= FrontendHelper::getPageView($value["id"], "knowledge") ?> คน</span>
                                            </div>
                                        </object>
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