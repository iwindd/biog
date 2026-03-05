<?php

/* @var $this yii\web\View */

use frontend\components\FrontendHelper;
use common\components\FileLibrary;
use frontend\models\Blog;

use frontend\models\Banner;
$banner = Banner::find()->where(['slug_url' => 'Blog'])->one();

$this->title = 'บล็อก';
$this->registerCss("nav {background-image: url('/images/banner/Blog_Banner.png'); }");

$linkMain = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST']; //
$_SESSION['currentUrl'] = $linkMain . $_SERVER['REQUEST_URI'];

?>
<?php $this->beginBlock('banner') ?>
<div class="section-banner">
    <?php if(!empty($banner->picture_path)): ?>
        <img src="/files/banner/<?php echo $banner->picture_path; ?>" class="banner ">
    <?php else: ?>
        <img src="/images/banner/Blog_Banner.png" class="banner ">
    <?php endif; ?>
</div>
<?php $this->endBlock() ?>

<div class="main-breadcrumb" aria-label="breadcrumb">
    <div class="container">
        <ol class="breadcrumb pl-0">
            <li class="breadcrumb-item home"><a href="/">Home</a></li>
            <li class="breadcrumb-item"><a href="/blog"><?= $this->title ?></a></li>
        </ol>
    </div>
</div>

<div class="site-blog" style="min-height: calc(100vh - 204px);">
    <div class="body-content">
        <section class="content pt-4">
            <div class="container">
                <div class="row">
                    <div class="menu-sidebar">
                        <p class="menu"></p>
                        <?php echo $this->render('../layouts/sidebar'); ?>
                    </div>
                    <div class="section-main">
                        <div class="d-flex">
                            <div class="col-6 pl-0">
                                <p class="menu text-left">บล็อกล่าสุด</p>
                            </div>
                            <div class="col-6 pr-0">
                                <?php  if(!empty(Yii::$app->user->id)){?>
                                    <a href="/blog/create" >
                                        <button class="btn btn-purple float-right"><img src="/images/icon/Create_Blog.svg" class="icon-btn">สร้างบล็อกใหม่</button>
                                    </a>
                                <?php }   ?>
                            </div>
                        </div>
                        <div class="blog-content">
                            <div class="row content-list">
                                <?php if (empty($blog)) { ?>
                                    <div class="container empty-data">
                                        <p class="text-center">ไม่พบข้อมูล</p>
                                    </div>
                                <?php } ?>
                                <?php foreach ($blog as $key => $value) {
                                    $thisUrl = '/blog/' . $value["id"];
                                ?>
                                    <a href="/blog/<?= $value["id"] ?>" class="col-lg-4 d-block mb-3">
                                        <object>
                                            <div class="content-img">
                                                <?php echo FileLibrary::getImageFrontend(Blog::UPLOAD_FOLDER_BLOG, $value["picture_path"]); ?>
                                            </div>
                                            <h1 class="title"><?php echo $value["title"]; ?></h1>
                                            <p class="short-desc">
                                                <?php echo strip_tags($value["description"]); ?>
                                            </p>
                                            <p class="creator-post">
                                                <?php echo FileLibrary::getImageFrontend('profile', FrontendHelper::getProfileImage($value["created_by_user_id"]), '', false, '', 'img-rounded-small'); ?>
                                                <?php echo FrontendHelper::getProfileName($value["created_by_user_id"]); ?>
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
                                                <span class="viewer"><i class="far fa-user"></i> ผู้เข้าชม <?= FrontendHelper::getPageView($value["id"], "blog") ?> คน</span>
                                            </div>
                                        </object>
                                    </a>
                                <?php } ?>
                            </div>
                            <div class="pagin justify-content-center mt-5">
                                <?php echo \yii\widgets\LinkPager::widget([
                                    'pagination' => $pagination,
                                    'maxButtonCount' => 4,
                                ]); ?>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>
    </div>
</div>