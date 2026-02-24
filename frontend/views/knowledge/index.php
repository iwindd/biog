<?php

use frontend\components\FrontendHelper;
use common\components\FileLibrary;
use frontend\models\Knowledge;
/* @var $this yii\web\View */

$this->title = 'องค์ความรู้ออนไลน์';
$this->registerCss("nav {background-image: url('/images/banner/Knowledge_Banner.svg'); }");

$linkMain = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST']; //
$_SESSION['currentUrl'] = $linkMain . $_SERVER['REQUEST_URI'];

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
            <li class="breadcrumb-item"><a href="/knowledge"><?= $this->title ?></a></li>
        </ol>
    </div>
</div>
<div class="site-knowledge" style="min-height: calc(100vh - 204px);">
    <div class="body-content">
        <section class="content mb-3 pt-4">
            <div class="container">
                <div class="row">
                    <div class="menu-sidebar">
                        <p class="menu"></p>
                        <?php echo $this->render('../layouts/sidebar'); ?>
                    </div>
                    <div class="section-main">
                        <div class="d-flex">
                            <div class="col-6 pl-0">
                                <p class="menu text-left">องค์ความรู้ออนไลน์</p>
                            </div>
                            <div class="col-6 pr-0">
                                <!-- <p class="menu text-right view-all">ดูทั้งหมด</p> -->
                            </div>
                        </div>

                        <div class="news-content">
                            <div class="row content-list">
                                <?php if (empty($knowledge_infographic)) { ?>
                                    <div class="container empty-data">
                                        <p class="text-center">ไม่พบข้อมูล</p>
                                    </div>
                                <?php } ?>
                                <?php foreach ($knowledge_infographic as $key => $value) {
                                    $thisUrl = '/knowledge/' . $value["id"];
                                ?>
                                    <a href="/knowledge/<?= $value["id"] ?>" class="col-lg-4 mb-3">
                                        <object>
                                            <div class="knowledge-img">
                                                <?php echo FileLibrary::getImageFrontend(Knowledge::UPLOAD_FOLDER_KNOWLEDGE, $value["picture_path"]); ?>
                                            </div>
                                            <h1 class="title"><?php echo $value["title"] ?></h1>
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
                                <?php  } ?>
                            </div>
                            <div class="pagin justify-content-center mt-5">
                                <?php echo \yii\widgets\LinkPager::widget([
                                    'pagination' => $pagination_infographic,
                                ]); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <section class="content-video mb-3 pb-4">
            <div class="container">
                <div class="content-video-list">
                    <div class="d-flex">
                        <div class="col-6 pl-0">
                            <p class="menu text-left">Videos</p>
                        </div>
                        <div class="col-6 pr-0">
                            <p class="menu text-right view-all"></p>
                        </div>
                    </div>
                    <div class="row">
                        <?php
                        if (empty($knowledge_videos)) {
                        ?>
                            <div class="container empty-data" style="background-color: #f1f2f2;">
                                <p class="text-center">ไม่พบข้อมูล</p>
                            </div>
                        <?php
                        }
                        ?>
                        <?php foreach ($knowledge_videos as $key => $value) {
                            $thisUrl = '/knowledge/' . $value["id"];
                        ?>
                            <a href="/knowledge/<?= $value["id"] ?>" class="col-lg-4">
                                <object>
                                    <div class="knowledge-img knowledge-video">
                                        <?php if(!empty($value['path']) && strpos($value['path'], 'youtube') !== false): ?>
                                            <iframe  height="250" src="<?php echo FrontendHelper::getYoutubeEmbedUrl($value['path']); ?>"></iframe>
                                        <?php else: ?>
                                            <?php echo FileLibrary::getImageFrontend(Knowledge::UPLOAD_FOLDER_KNOWLEDGE, $value["picture_path"]); ?>
                                        <?php endif; ?>
                                    </div>
                                    <h1 class="title"><?php echo $value["title"] ?></h1>
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
                    <div class="pagin justify-content-center mt-5">
                        <?php echo \yii\widgets\LinkPager::widget([
                            //'options'  =>array(),
                            'pagination' => $pagination_videos,
                        ]); ?>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>