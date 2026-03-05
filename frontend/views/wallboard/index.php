<?php

/* @var $this yii\web\View */

use frontend\components\FrontendHelper;
use common\components\FileLibrary;

$this->title = 'Wallboard';
$this->registerCss("nav {background-image: url('/images/banner/News_Banner.png'); }");

?>
<?php $this->beginBlock('banner') ?>
<div class="section-banner">
    <img src="/images/banner/News_Banner.png" class="banner">
</div>
<?php $this->endBlock() ?>

<div class="main-breadcrumb" aria-label="breadcrumb">
    <div class="container">
        <ol class="breadcrumb pl-0">
            <li class="breadcrumb-item home"><a href="/">Home</a></li>
            <li class="breadcrumb-item"><a href="/wallboard"><?= $this->title ?></a></li>
        </ol>
    </div>
</div>


<div class="site-fungi" style="min-height: calc(100vh - 204px);">
    <div class="body-content">
        <section class="content pt-4">
            <div class="container">
                <div class="row">
                    <div class="menu-sidebar">
                        <p class="menu">เมนู</p>
                        <?php echo $this->render('../layouts/sidebar'); ?>
                    </div>
                    <div class="section-main">
                        <div class="d-bock">

                            <p class="menu text-left">Wallboard</p>


                        </div>
                        <div class="blog-content">
                            <div class="row content-list pt-0 ">
                                <div class="wall-board-bg mt-0 w-100">
                                    <?php if (empty($wallboard)) { ?>
                                        <div class="container empty-data">
                                            <p class="text-center">ไม่พบข้อมูล</p>
                                        </div>
                                    <?php }
                                    foreach ($wallboard as $key => $value) { ?>
                                        <div class="wall-board-content" data-id="<?= $value['id'] ?>">
                                            <div class="row">
                                                <?php if (!empty(Yii::$app->user->identity->id)) : ?>
                                                    <?php if (Yii::$app->user->identity->id == $value['created_by_user_id']) : ?>
                                                        <span class="option-comment">
                                                            <!-- <i class="fas fa-ellipsis-h"></i> -->
                                                            <i class="delete-wallbaord fas fa-trash-alt"></i>
                                                        </span>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                                <div class="pl-3 pr-3 d-flex">
                                                        <?php echo FileLibrary::getImageFrontend('profile', FrontendHelper::getProfileImage($value["created_by_user_id"]), '', false, '', 'img-rounded'); ?>
                                                    </div>
                                                    <div class="d-flex" style="flex: 1;flex-direction: column;">
                                                        <p class="title-user"><?php echo FrontendHelper::getProfileName($value["created_by_user_id"]); ?></p>
                                                        <?php if (!empty(FrontendHelper::getSchoolName($value["created_by_user_id"]))) { ?>
                                                            <p class="school">รร. <?= FrontendHelper::getSchoolName($value["created_by_user_id"]) ?></p>
                                                        <?php } ?>
                                                        <p class="post text-info">โพสต์ <?php echo FrontendHelper::getTime($value["created_at"]); ?><span class="post-date">วันที่ <?php echo FrontendHelper::getDate($value["created_at"]); ?></span></p>
                                                    </div>
                                            </div>

                                            <p class="description"><?php echo $value['description'] ?></p>
                                        </div>
                                    <?php } ?>
                                </div>
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