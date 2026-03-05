<?php

/* @var $this yii\web\View */

use frontend\components\FrontendHelper;
use frontend\components\PermissionAccess;
use common\components\FileLibrary;
use frontend\models\content\Content;

$this->title = 'ภูมิปัญญา / ปราชญ์ Expert';
$this->registerCss("nav {background-image: url('/images/banner/Data_Banner.png'); }");

$linkMain = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST']; //
$_SESSION['currentUrl'] = $linkMain . $_SERVER['REQUEST_URI'];

use frontend\models\Banner;
$banner = Banner::find()->where(['slug_url' => 'Content Expert'])->one();


//$linkMain = "https://biogang.devfunction.com";
?>
<?php $this->beginBlock('banner') ?>
<div class="section-banner">
    <?php if(!empty($banner->picture_path)): ?>
        <img src="/files/banner/<?php echo $banner->picture_path; ?>" class="banner ">
    <?php else: ?>
        <img src="/images/banner/Data_Banner.png" class="banner ">
    <?php endif; ?>
</div>
<?php $this->endBlock() ?>

<div class="main-breadcrumb" aria-label="breadcrumb">
    <div class="container">
        <ol class="breadcrumb pl-0">
            <li class="breadcrumb-item home"><a href="/">Home</a></li>
            <li class="breadcrumb-item"><a href="/expert"><?= $this->title ?></a></li>
        </ol>
    </div>
</div>


<div class="site-fungi" style="min-height: calc(100vh - 204px);">
    <div class="body-content">
        <section class="content pt-4">
            <div class="container">
                <div class="row">
                    <div class="menu-sidebar">
                        <p class="menu"></p>
                        <?php echo $this->render('../layouts/sidebar'); ?>
                    </div>
                    <div class="section-main">
                        <div class="d-bock">
                            <p class="menu text-left">ฐานข้อมูล: ภูมิปัญญา / ปราชญ์ Expert</p>
                            <div class="row">
                                <div class="container mb-3">
                                    <a href="/content-plant" class="btn btn-outline-soft-green  mr-1 mb-3">พืช</a>
                                    <a href="/content-animals" class="btn btn-outline-soft-green mr-1 mb-3">สัตว์</a>
                                    <a href="/content-fungi" class="btn btn-outline-soft-green mr-1 mb-3">จุลินทรีย์</a>
                                    <a href="/content-expert" class="btn btn-outline-soft-green active mr-1 mb-3">ภูมิปัญญา / ปราชญ์ Expert</a>
                                    <a href="/content-ecotourism" class="btn btn-outline-soft-green  mr-1 mb-3">การท่องเที่ยวเชิงนิเวศ</a>
                                    <a href="/content-product" class="btn btn-outline-soft-green  mr-1 mb-3">ผลิตภัณฑ์ชุมชน</a>
                                    <?php if(PermissionAccess::FrontendAccess('student_create_content', 'function')): ?>
                                    <a href="/content/create/expert" class="btn btn-purple float-right"><img src="/images/icon/Upload_Data.svg" class="icon-btn">การนำเข้าข้อมูลของฉัน</a>
                                    <?php endif; ?>
                                </div>
                            </div>

                        </div>
                        <div class="blog-content">
                            <div class="row content-list">
                                <?php if (empty($expert)) { ?>
                                    <div class="container empty-data">
                                        <p class="text-center">ไม่พบข้อมูล</p>
                                    </div>
                                <?php }
                                foreach ($expert as $key => $value) { 
                                    $thisUrl = '/content-expert/' . $value["id"];
                                ?>
                                    <a href="/content-expert/<?= $value["id"] ?>" class="col-lg-4 mb-3">
                                        <object>    
                                            <div class="content-img">
                                                <?php echo FileLibrary::getImageFrontend(Content::UPLOAD_FOLDER_CONTENT_EXPERT, $value["picture_path"]);?>
                                            
                                            </div>
                                            <h1 class="title"><?php echo $value["name"]; ?></h1>
                                            <p class="short-desc"><?php echo strip_tags($value["description"]); ?></p>
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
                                                <span class="viewer"><i class="far fa-user"></i> ผู้เข้าชม <?= FrontendHelper::getPageView($value["id"], "content") ?> คน</span>
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