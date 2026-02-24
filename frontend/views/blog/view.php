<?php

/* @var $this yii\web\View */

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use frontend\components\FrontendHelper;
use frontend\models\Blog;
use yii\helpers\Url;
use common\components\FileLibrary;
use frontend\models\BlogComment;

$this->title = $blog["title"];
$this->registerCss("nav {background-image: url('/images/banner/Blog_Banner.png'); }");
$this->registerCssFile(Url::base().'/js/gallery/jquery.fancybox.min.css', ['depends' => [\frontend\assets\AppAsset::className()]]);
$this->registerCssFile(Url::base().'/js/gallery/gallery.css', ['depends' => [\frontend\assets\AppAsset::className()]]);
$this->registerJsFile(Url::base().'/js/gallery/jquery.fancybox.min.js', ['depends' => [\frontend\assets\AppAsset::className()]]);
$this->registerJsFile(Url::base().'/js/gallery/gallery.js', ['depends' => [\frontend\assets\AppAsset::className()]]);
$dependsAppAsset = ['depends' => 'frontend\assets\AppAsset'];
$googleMapKey = 'AIzaSyB47Udr0aU-iYppKBpcjRyKLYZaGPIlHVw';
$callbackFucntionName = 'showMapContact';
$googleApiUrl = 'https://maps.googleapis.com/maps/api/js?key=' . $googleMapKey . '&callback=' . $callbackFucntionName;
$this->registerJsFile($googleApiUrl, $dependsAppAsset);

$linkMain = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST']; //
$_SESSION['currentUrl'] = $linkMain . $_SERVER['REQUEST_URI'];
$thisUrl = '/blog/' . $blog["id"];
//$linkMain = "https://biogang.devfunction.com";
$url = $linkMain . $thisUrl;

$curl = curl_init("http://developers.facebook.com/tools/debug/og/object?q=" . $url);
curl_setopt($curl, CURLOPT_HEADER, 0);

curl_exec($curl);
curl_close($curl);

$pathImage = $blog["picture_path"];
echo FrontendHelper::getMetaImage("blog", $pathImage);
echo FrontendHelper::getMetaTitle($this->title);
echo FrontendHelper::getDescription(strip_tags($blog["description"]));
echo FrontendHelper::getUrl($url);

use frontend\models\Banner;
$banner = Banner::find()->where(['slug_url' => 'Blog'])->one();


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
            <li class="breadcrumb-item"><a href="/blog">บล็อก</a></li>
            <li class="breadcrumb-item"><a href="/blog/<?php echo $blog["id"]; ?>"><?php echo $blog["title"]; ?></a></li>
        </ol>
    </div>
</div>


<div class="site-blog-detail" style="min-height: calc(100vh - 204px);">
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
                            <div class="col-lg-8">
                                <p class="menu text-left"><?php echo $blog["title"]; ?></p>
                                <?php echo FileLibrary::getImageFrontend(Blog::UPLOAD_FOLDER_BLOG, $blog["picture_path"]); ?>
                                <div class="description  fr-view"><?php echo $blog["description"]; ?></div>

                                <div class="image-galley">
                                    <div class="page-top">
                                        <div class="row">
                                            <?php foreach ($picture as $key => $value) {
                                                ?>
                                                    <div class="col-lg-4 col-md-4 col-6 thumb">
                                                        <a href="<?php echo FrontendHelper::blogImage($value['path'], 'blog'); ?>"
                                                            class="fancybox" rel="ligthbox">
                                                            <img src="<?php echo FrontendHelper::blogImage($value['path'], 'blog'); ?>"
                                                                class="zoom img-fluid " alt="">
                                                        </a>
                                                    </div>
                                            <?php
                                                }?>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                        <?php if(!empty($files)): ?>
                                            <div class="col-12 mb-1" >
                                                <p class="description ">เอกสารดาวน์โหลด:</p>
                                            </div>
                                        <?php endif; ?>
                                        <?php foreach ($files as $key => $value) { ?>
                                        <div class="col-12 mb-3" >
                                            <a download href="/files/blog/<?=$value["path"]?>" target="_blank" class="btn btn-download">
                                                <i class="fas fa-download"></i> <?php echo $value["name"]; ?>
                                            </a>
                                        </div>
                                        <?php } ?>
                                    
                                    
                                </div>


                               
                                
                                <?php if(!empty($blog['video_url'])): ?>
                                    <div class="mb-3">
                                        <div class=" justify-content-center ">
                                            <?php if(strpos($blog['video_url'], 'youtube') !== false): ?>
                                                
                                                <iframe style="border: 0" width="425" height="350" src="<?php echo FrontendHelper::getYoutubeEmbedUrl($blog['video_url']); ?>"></iframe>

                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <?php if(!empty($blog['source_information'])): ?>
                                    <p class="description mb-5">แหล่งข้อมูล: <?php echo $blog["source_information"]; ?></p>
                                <?php endif; ?>

                                <div class="creator-post-block d-flex">
                                    <div class="profile-pic pr-2">
                                    <?php echo FileLibrary::getImageFrontend('profile', FrontendHelper::getProfileImage($blog["created_by_user_id"]), '', false, '', 'img-rounded'); ?>
                                    </div>
                                    <div class="profile-timestamp">
                                        <p class="title-user">
                                            <?php echo FrontendHelper::getProfileName($blog["created_by_user_id"]); ?>
                                        </p>
                                        <p class="post">โพสต์
                                            <?php echo FrontendHelper::getTime($blog["created_at"]); ?> น.
                                                วันที่
                                            <?php echo FrontendHelper::getDate($blog["created_at"]); ?>
                                        </p>
                                    </div>
                                </div>
                                
                            </div>
                            <div class="col-lg-4">
                                <p class="menu text-left">แสดงความคิดเห็น</p>
                                <div class="section-comment">
                                    <div class="comment-header">
                                        <div class="post">
                                            <span class="viewer"><i class="far fa-user"></i> ผู้เข้าชมทั้งหมด <?= FrontendHelper::getPageView($blog["id"], "blog") ?> คน</span>
                                            <?php if (!empty(Yii::$app->user->id)) { ?>
                                                <span class="like <?php echo FrontendHelper::showLike($blog["id"], 'blog'); ?>" onclick="likeSubmit(<?= $blog['id'] ?>, 'blog')"><i class="far fa-thumbs-up"></i> ชื่นชอบ </span>
                                            <?php } ?>
                                            <span class="dropdown share">
                                                <span class="share-label"><i class="fas fa-share"></i> แชร์</span>
                                                <div class="dropdown-content share-dropdown-item">
                                                    <a class="fb-share" data-href="<?php echo $linkMain . $thisUrl; ?>" target="_blank" href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $linkMain . $thisUrl; ?>&amp;src=sdkpreparse&display=popup"><i class="fab fa-facebook-square"></i> Facebook
                                                    </a>
                                                    <a class="line-share" data-href="<?php echo $linkMain . $thisUrl; ?>" href="https://lineit.line.me/share/ui?url=<?php echo $linkMain . $thisUrl; ?>" target="_blank" data-url="<?php echo $linkMain . $thisUrl; ?>"><i class="fab fa-line"></i> Line</a>
                                                </div>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="comment-form-block">
                                        <?php if (!Yii::$app->user->isGuest) { ?>
                                            <form action="" id="comment-form" class="comment-form pt-3">
                                                <p><i class="far fa-comment"></i><span class="ml-3">แสดงความคิดเห็น</span></p>
                                                <div class="">
                                                    <input type="hidden" id="blog-id" value="<?= $blog["id"] ?>">
                                                    <textarea name="message" id="comment-input" rows="3" class="comment-input px-4" placeholder="แสดงความคิดเห็น"></textarea>
                                                </div>
                                                <div class="text-right">
                                                    <button type="button" class="btn btn-primary btn-post mt-3">โพสต์</button>
                                                </div>
                                            </form>
                                        <?php } else { ?>
                                            <p class="comment-form  pt-3 text-center">ต้องการแสดงความคิดเห็น กรุณา <a href="/user/login">เข้าสู่ระบบ</a></p>
                                        <?php } ?>
                                    </div>

                                    <?php if (empty($blogComment)) { ?>
                                        <div class="block-comment no-comment">
                                            <div class="empty-data">
                                                <p class="text-center">ยังไม่มีการแสดงความคิดเห็นใดๆ</p>
                                            </div>
                                        </div>
                                    <?php } else { ?>
                                        <div class="block-comment">
                                            <?php foreach ($blogComment as $key => $value) { ?>
                                                <div class="comment-list blog-comment-list" data-id="<?=$value["id"]?>">
                                                    <div class="row">
                                                        <?php if(Yii::$app->user->id == $value["user_id"]) {?>
                                                        <span class="option-comment">
                                                            <!-- <i class="fas fa-ellipsis-h"></i> -->
                                                            <i class="fas fa-trash-alt"></i>
                                                        </span>
                                                        <?php } ?>
                                                        <div class="profile-pic">
                                                            <?php echo FileLibrary::getImageFrontend('profile', FrontendHelper::getProfileImage($value["user_id"]), '', false, '', 'img-rounded'); ?>
                                                        </div>
                                                        <div class="profile-timestamp">
                                                            <p class="title-user">
                                                                <?php echo FrontendHelper::getProfileName($value["user_id"]); ?>
                                                            </p>
                                                            <p class="post">โพสต์
                                                                <?php echo FrontendHelper::getTime($value["created_at"]); ?>น.
                                                                วันที่
                                                                <?php echo FrontendHelper::getDate($value["created_at"]); ?></p>
                                                        </div>
                                                    </div>
                                                    <p class="message"><?php echo $value["message"] ?></p>
                                                </div>
                                            <?php } ?>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </section>
        <!-- <section class="sub-content">
            <div class="container">
                <div class="row">
                    <div class="menu-sidebar"></div>
                    <div class="section-main">
                        <div class="row">
                            <div class="col-lg-8">
                                <div id="map"></div>
                            </div>
                            <div class="col-lg-4">
                                <div class="address">
                                    <p class="menu text-left">ที่อยู่</p>
                                    <p class="title mb-2">ศูนย์เพาะพันธุ์ไม้ดอกไม้ประดับ คลอง 15</p>
                                    <p class="address-no mb-1">เลขที่ 182/88 หมู่ 1 ถนนสุวรรณศร ต.ท่าช้าง อ.เมือง จ.นครนายก 26000</p>
                                    <p class="tel mb-1">โทรศัพท์: 037-312282, 037-312284, 1672</p>
                                    <p class="tel mb-1">โทรสาร: 037-312286</p>
                                    <p class="tel mb-1">อีเมล: tatnayok@tat.or.th</p>
                                    <p class="tel mb-1">เว็บไซต์: www.tat8.com</p>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section> -->
        <section class="content-list">
            <div class="container">
                <div class="row">
                    <div class="menu-sidebar"></div>
                    <div class="section-main">
                        <p class="menu text-left">บล็อกล่าสุด</p>
                        <div class="row">
                            <?php foreach ($otherBlog as $key => $value) { 
                                $thisUrl = '/blog/' . $value["id"];
                            ?>
                                <a href="/blog/<?= $value["id"] ?>" class="col-lg-4 mb-3 blog-item">
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
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>