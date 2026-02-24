<?php

/* @var $this yii\web\View */

use common\components\FileLibrary;
use frontend\components\FrontendHelper;
use frontend\components\GoogleMapHelper;
use frontend\models\content\Content;
use yii\helpers\Url;

$this->title = $ecotourism['name'];
// $this->registerCss("nav {background-image: url('/images/banner/Data_Banner.png'); }");
// $this->registerJsFile('@web/js/content/google-map-content.js', ['depends' => \yii\web\JqueryAsset::className()]);
// $this->registerJsFile(GoogleMapHelper::getGoogleMapApiUrl(), ['depends' => \yii\web\JqueryAsset::className(), 'async' => true, 'defer' => true]);
$this->registerCss("nav {background-image: url('/images/banner/Data_Banner.png'); }");
$this->registerCssFile(Url::base() . '/js/gallery/jquery.fancybox.min.css', ['depends' => [\frontend\assets\AppAsset::className()]]);
$this->registerCssFile(Url::base() . '/js/gallery/gallery.css', ['depends' => [\frontend\assets\AppAsset::className()]]);
$this->registerJsFile(Url::base() . '/js/gallery/jquery.fancybox.min.js', ['depends' => [\frontend\assets\AppAsset::className()]]);
$this->registerJsFile(Url::base() . '/js/gallery/gallery.js', ['depends' => [\frontend\assets\AppAsset::className()]]);

$this->registerJsFile('@web/js/content/google-map-content.js', ['depends' => \yii\web\JqueryAsset::className()]);
$this->registerJsFile(GoogleMapHelper::getGoogleMapApiUrl(), ['depends' => \yii\web\JqueryAsset::className(), 'async' => true, 'defer' => true]);
$this->registerJsFile('@web/js/content/content.js', ['depends' => \yii\web\JqueryAsset::className()]);

$linkMain = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];  //
$_SESSION['currentUrl'] = $linkMain . $_SERVER['REQUEST_URI'];
$thisUrl = '/content-ecotourism/' . $ecotourism['id'];
// $linkMain = "https://biogang.devfunction.com";
$url = $linkMain . $thisUrl;

$curl = curl_init('http://developers.facebook.com/tools/debug/og/object?q=' . $url);
curl_setopt($curl, CURLOPT_HEADER, 0);

curl_exec($curl);
curl_close($curl);

$pathImage = $ecotourism['picture_path'];
echo FrontendHelper::getMetaImage('content-ecotourism', $pathImage);
echo FrontendHelper::getMetaTitle($this->title);
echo FrontendHelper::getDescription(strip_tags($ecotourism['description']));
echo FrontendHelper::getUrl($url);

if (!empty($pathImage)) {
    $this->registerCss("section.sub-content {background: linear-gradient(rgb(98, 58, 179, 50%), rgba(98, 58, 179, 0.5)), url('/files/content-ecotourism/" . $pathImage . "')}");
} else {
    $this->registerCss("section.sub-content {background: linear-gradient(rgb(98, 58, 179, 50%), rgba(98, 58, 179, 0.5)), url('/images/banner/Data_Banner.png')}");
}

use frontend\models\Banner;

$banner = Banner::find()->where(['slug_url' => 'Content Ecotourism'])->one();

?>
<?php $this->beginBlock('banner') ?>
<div class="section-banner">
    <?php if (!empty($banner->picture_path)): ?>
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
            <li class="breadcrumb-item"><a href="/content-ecotourism">การท่องเที่ยวเชิงนิเวศ</a></li>
            <li class="breadcrumb-item"><a href="/content-ecotourism/<?php echo $ecotourism['id']; ?>"><?php echo $ecotourism['name']; ?></a></li>
        </ol>
    </div>
</div>


<div class="site-content-detail" style="min-height: calc(100vh - 204px);">
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
                                <p class="menu text-left title-text"><?php echo $ecotourism['name']; ?></p>
                                <?php echo FileLibrary::getImageFrontend(Content::UPLOAD_FOLDER_CONTENT_ECOTOURISM, $ecotourism['picture_path'], '', false, '', 'w-100 mb-3'); ?>
                                <?php if (!empty($ecotourism['description'])): ?>
                                    <p class="detail-title"><span>รายละเอียด:</span> <?php echo $ecotourism['description']; ?></p>
                                <?php endif; ?>
                                <?php if (!empty($content_ecotourism['travel_information'])): ?>
                                    <p class="detail-title fr-view"><span><?= $content_ecotourism->getAttributeLabel('travel_information'); ?>:</span> <?php echo $content_ecotourism['travel_information']; ?></p>
                                <?php endif; ?>
                                <?php if (!empty($content_ecotourism['name'])): ?>
                                    <p class="detail-title"><span><?= $content_ecotourism->getAttributeLabel('name'); ?>:</span> <?php echo $content_ecotourism['name']; ?></p>
                                <?php endif; ?>
                                <?php if (!empty($content_ecotourism['contact'])): ?>
                                    <p class="detail-title"><span><?= $content_ecotourism->getAttributeLabel('contact'); ?>:</span> <?php echo $content_ecotourism['contact']; ?></p>
                                <?php endif; ?>
                                <?php if (!empty($content_ecotourism['address'])): ?>
                                    <p class="detail-title"><span><?= $content_ecotourism->getAttributeLabel('address'); ?>:</span> <?php echo $content_ecotourism['address']; ?></p>
                                <?php endif; ?>
                                <?php if (!empty(FrontendHelper::getAddress($ecotourism['province_id'], $ecotourism['district_id'], $ecotourism['subdistrict_id'], $ecotourism['zipcode_id']))): ?>
                                    <p class="detail-title"><span>สถานที่ตั้ง:</span>
                                        <?php echo FrontendHelper::getAddress($ecotourism['province_id'], $ecotourism['district_id'], $ecotourism['subdistrict_id'], $ecotourism['zipcode_id']); ?>
                                    </p>
                                <?php endif; ?>
                                <?php if (!empty($content_ecotourism['phone'])): ?>
                                    <p class="detail-title"><span><?= $content_ecotourism->getAttributeLabel('phone'); ?>:</span> <?php echo $content_ecotourism['phone']; ?></p>
                                <?php endif; ?>
                                <?php if (!empty($ecotourism['other_information'])): ?>
                                    <p class="detail-title fr-view"><span>ข้อมูลอื่นๆ ที่ฉันรู้:</span> <?php echo $ecotourism['other_information']; ?></p>
                                <?php endif; ?>
   

                                <?php if (!empty($ecotourism['photo_credit'])): ?>
                                    <p class="detail-title"><span>แหล่งที่มาของภาพ:</span> <?php echo FrontendHelper::getSourceInformation($ecotourism['photo_credit']); ?></p>
                                <?php endif; ?>

                                <?php if (!empty($ecotourism['source_information'])): ?>
                                    <p class="detail-title"><span>แหล่งที่มาของข้อมูล:</span> <?php echo FrontendHelper::getSourceInformation($ecotourism['source_information']); ?></p>
                                <?php endif; ?>

                                <?php if (!empty($ecotourism['license_id']) && !empty($ecotourism->license)): ?>
                                    <p class="detail-title"><span>สัญญาอนุญาต:</span> <?php echo $ecotourism->license->name; ?></p>
                                <?php endif; ?>

                                <?php if (!empty(FrontendHelper::getTaxonomyName($ecotourism['id']))): ?>
                                    <p class="detail-title"><span>คำช่วยค้นหา:</span> <?php echo FrontendHelper::getTaxonomyName($ecotourism['id']); ?></p>
                                <?php endif; ?>
                                <?php  /* if(!empty($ecotourism["latitude"]) && !empty($ecotourism["longitude"])) {?>
                                 <input id="content-latitude" type="hidden" value="<?=$ecotourism["latitude"]?>">
                                 <input id="content-longitude" type="hidden" value="<?=$ecotourism["longitude"]?>">
                                 <div class="row">
                                     <div class="col">
                                         <div id="content-google-map" class="content-google-map" style="min-height:400px;"></div>
                                     </div>
                                 </div>
                                 <?php } */
                                ?>
                                <div class="image-galley">
                                    <div class="page-top">
                                        <div class="row">
                                            <?php
                                            foreach ($picture as $key => $value) {
                                                ?>
                                                <div class="col-lg-4 col-md-4 col-6 thumb">
                                                    <a href="<?php echo FrontendHelper::contentImage($value['path'], 'ecotourism'); ?>" class="fancybox" rel="ligthbox">
                                                        <img src="<?php echo FrontendHelper::contentImage($value['path'], 'ecotourism'); ?>" class="zoom img-fluid " alt="">
                                                    </a>
                                                </div>
                                            <?php
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="creator-post-block d-flex">
                                    <div class="profile-pic pr-2">
                                    <?php echo FileLibrary::getImageFrontend('profile', FrontendHelper::getProfileImage($ecotourism['created_by_user_id']), '', false, '', 'img-rounded'); ?>
                                    </div>
                                    <div class="profile-timestamp">
                                        <p class="title-user">
                                                                <?php echo FrontendHelper::getProfileName($ecotourism['created_by_user_id']); ?>
                                        </p>
                                        <p class="post">โพสต์
                                            <?php echo FrontendHelper::getTime($ecotourism['created_at']); ?> น.
                                                วันที่
                                            <?php echo FrontendHelper::getDate($ecotourism['created_at']); ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <p class="menu text-left">แสดงความคิดเห็น</p>
                                <div class="section-comment">
                                    <div class="comment-header">
                                        <div class="post">
                                            <span class="viewer"><i class="far fa-user"></i> ผู้เข้าชมทั้งหมด <?= FrontendHelper::getPageView($ecotourism['id'], 'content') ?> คน</span>
                                            <?php if (!empty(Yii::$app->user->id)) { ?>
                                                <span class="like <?php echo FrontendHelper::showLike($ecotourism['id'], 'content'); ?>" onclick="likeSubmit(<?= $ecotourism['id'] ?>, 'content')"><i class="far fa-thumbs-up"></i> ชื่นชอบ </span>
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
                                            <form action="" id="comment-form" class="comment-form pt-0">
                                                <p><i class="far fa-comment"></i><span class="ml-3">แสดงความคิดเห็น</span></p>
                                                <div class="">
                                                    <input type="hidden" id="content-id" value="<?= $ecotourism['id'] ?>">
                                                    <textarea name="message" id="comment-input" rows="3" class="comment-input px-4" placeholder="แสดงความคิดเห็น"></textarea>
                                                </div>
                                                <div class="text-right">
                                                    <button type="button" class="btn btn-primary btn-content-post mt-3">โพสต์</button>
                                                </div>
                                            </form>
                                        <?php } else { ?>
                                            <p class="comment-form  pt-3 text-center">ต้องการแสดงความคิดเห็น กรุณา <a href="/user/login">เข้าสู่ระบบ</a></p>
                                        <?php } ?>
                                    </div>

                                    <?php if (empty($contentComment)) { ?>
                                        <div class="block-comment no-comment">
                                            <div class="empty-data">
                                                <p class="text-center">ยังไม่มีการแสดงความคิดเห็นใดๆ</p>
                                            </div>
                                        </div>
                                    <?php } else { ?>
                                        <div class="block-comment">
                                            <?php foreach ($contentComment as $key => $value) { ?>
                                                <div class="comment-list" data-id="<?= $value['id'] ?>">
                                                    <div class="row">
                                                        <?php if (Yii::$app->user->id == $value['user_id']) { ?>
                                                            <span class="option-comment">
                                                                <!-- <i class="fas fa-ellipsis-h"></i> -->
                                                                <i class="fas fa-trash-alt"></i>
                                                            </span>
                                                        <?php } ?>
                                                        <div class="profile-pic">
                                                            <?php echo FileLibrary::getImageFrontend('profile', FrontendHelper::getProfileImage($value['user_id']), '', false, '', 'img-rounded'); ?>
                                                        </div>
                                                        <div class="profile-timestamp">
                                                            <p class="title-user">
                                                                <?php echo FrontendHelper::getProfileName($value['user_id']); ?>
                                                            </p>
                                                            <p class="post">โพสต์
                                                                <?php echo FrontendHelper::getTime($value['created_at']); ?>น.
                                                                วันที่
                                                                <?php echo FrontendHelper::getDate($value['created_at']); ?></p>
                                                        </div>
                                                    </div>
                                                    <p class="message"><?php echo $value['message'] ?></p>
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

        <section class="sub-content">
            <div class="container">
                <div class="row">
                    <div class="menu-sidebar"></div>
                    <div class="section-main">
                        <div class="row">
                            <div class="col-lg-8">
                                <input type="hidden" id="content-latitude" value="<?php echo $ecotourism['latitude']; ?>">
                                <input type="hidden" id="content-longitude" value="<?php echo $ecotourism['longitude']; ?>">

                                <input type="hidden" id="page-type" value="ecotourism">

                                <div id="content-google-map" class="content-google-map" style="min-height:400px;"></div>
                            </div>
                            <div class="col-lg-4">
                                <div class="address">
                                    <p class="menu text-left">ที่อยู่</p>
                                    <p class="detail-title"><span></span>
                                        <?php echo FrontendHelper::getAddress($ecotourism['province_id'], $ecotourism['district_id'], $ecotourism['subdistrict_id'], $ecotourism['zipcode_id']); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="content-list">
            <div class="container">
                <div class="row">
                    <div class="menu-sidebar"></div>
                    <div class="section-main">
                        <p class="menu text-left">การท่องเที่ยวเชิงนิเวศล่าสุด</p>
                        <div class="row">
                            <?php if (empty($other_content_ecotourism)) { ?>
                                <div class="container empty-data">
                                    <p class="text-center">ไม่พบข้อมูล</p>
                                </div>
                            <?php } ?>
                            <?php
                            foreach ($other_content_ecotourism as $key => $value) {
                                $thisUrl = '/content-ecotourism/' . $value['id'];
                                ?>
                                <a href="/content-ecotourism/<?= $value['id'] ?>" class="col-lg-4 mb-3">
                                    <object>
                                        <div class="content-img">
                                            <?php echo FileLibrary::getImageFrontend(Content::UPLOAD_FOLDER_CONTENT_ECOTOURISM, $value['picture_path']); ?>
                                        </div>
                                        <h1 class="title"><?php echo $value['name']; ?></h1>
                                        <p class="short-desc">
                                            <?php echo strip_tags($value['description']); ?>
                                        </p>
                                        <p class="creator-post">
                                            <?php echo FileLibrary::getImageFrontend('profile', FrontendHelper::getProfileImage($value['created_by_user_id']), '', false, '', 'img-rounded-small'); ?>
                                            <?php echo FrontendHelper::getProfileName($value['created_by_user_id']); ?>
                                        </p>
                                        <div class="post">โพสต์ <?php echo FrontendHelper::getTime($value['created_at']); ?>
                                            <span class="post-date">วันที่ <?php echo FrontendHelper::getDate($value['created_at']) ?> </span>
                                            <span class="dropdown share">
                                                <span class="share-label"><i class="fas fa-share"></i> แชร์</span>
                                                <div class="dropdown-content share-dropdown-item">
                                                    <a class="fb-share" data-href="<?php echo $linkMain . $thisUrl; ?>" target="_blank" href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $linkMain . $thisUrl; ?>&amp;src=sdkpreparse&display=popup"><i class="fab fa-facebook-square"></i> Facebook
                                                    </a>
                                                    <a class="line-share" data-href="<?php echo $linkMain . $thisUrl; ?>" href="https://lineit.line.me/share/ui?url=<?php echo $linkMain . $thisUrl; ?>" target="_blank" data-url="<?php echo $linkMain . $thisUrl; ?>"><i class="fab fa-line"></i> Line</a>
                                                </div>
                                            </span>
                                            <span class="viewer"><i class="far fa-user"></i> ผู้เข้าชม <?= FrontendHelper::getPageView($value['id'], 'content') ?> คน</span>
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