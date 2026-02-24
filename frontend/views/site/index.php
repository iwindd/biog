<?php

/* @var $this yii\web\View */

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use frontend\models\Knowledge;
use yii\bootstrap4\ActiveForm;
use common\components\FileLibrary;
use frontend\components\FrontendHelper;
use frontend\components\PermissionAccess;

$this->title = 'BIOGANG';


$this->registerCssFile('@web/jqcloud/jqcloud.css', ['depends' => 'frontend\assets\AppAsset']);
$this->registerJsFile('@web/jqcloud/jqcloud.js', ['depends' => 'frontend\assets\AppAsset']);

$this->registerJsFile('https://unpkg.com/vis-network/standalone/umd/vis-network.min.js', ['position' => View::POS_HEAD]);
$this->registerJsFile('@web/js/visualization/wordcloud-keywordmap.js');

$linkMain = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST']; //
$_SESSION['currentUrl'] = $linkMain . $_SERVER['REQUEST_URI'];
?>
<div id="fb-root"></div>
<script async defer crossorigin="anonymous" src="https://connect.facebook.net/th_TH/sdk.js#xfbml=1&version=v8.0" nonce="IMO8ymNg"></script>
<?php $this->beginBlock('banner') ?>
<div class="section-banner">
    <img src="/images/banner/home-banner.png" class="banner">
</div>

<?php $this->endBlock() ?>

<!-- <div class="main-breadcrumb" aria-label="breadcrumb">
    <div class="container">
        <ol class="breadcrumb pl-0">
            <li class="breadcrumb-item home"><a href="#">Home</a></li>
        </ol>
    </div>
</div> -->
<div class="site-index" style="min-height: calc(100vh - 204px);">
    <div class="body-content">
        <section class="content pt-4">
            <div class="container">
                <div class="row">
                    <div class="menu-sidebar">
                        <p class="menu"></p>
                        <?php echo $this->render('../layouts/sidebar'); ?>
                    </div>
                    <div class="section-main">
                        <div class="row">
                            <div class="col-lg-8">
                                <p class="menu text-left">Wall boards</p>
                                <div class="row">
                                    <div class="col-6">
                                        <?php if (PermissionAccess::FrontendAccess('add_wallboard', 'function')) { ?>
                                            <button class="btn btn-success border-radius w-100 text-left mb-2 text-center" data-toggle="modal" data-target="#exampleModal"><i class="fas fa-pencil-alt"></i> <span> เขียนบอร์ดใหม่</span></button>
                                        <?php } ?>
                                    </div>
                                </div>

                                <div class="wall-board-bg mt-0">
                                    <?php if (empty($wallbaord)) { ?>
                                        <div class="wall-board-content mb-0">
                                            <p class="text-center mb-0">ไม่พบข้อมูล</p>
                                        </div>
                                        <?php } else {
                                        foreach ($wallbaord as $key => $value) {
                                        ?>
                                            <div class="wall-board-content" data-id="<?=$value['id']?>">
                                                <div class="row">
                                                <?php if(!empty(Yii::$app->user->identity->id)): ?>
                                                    <?php if(Yii::$app->user->identity->id == $value['created_by_user_id']): ?>
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

                                                <div class="description fr-view"><?php echo $value['description'] ?></div>
                                            </div>
                                        <?php }
                                    }
                                    if (count($wallbaord) >= 5) {
                                        ?>
                                        <div class="d-block text-right">
                                            <a href="/wallboard" class="btn btn-warning pull-right">เพิ่มเติม ››</a>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <p class="menu text-left">ติดตาม</p>
                                <div class="fb-page" data-href="https://www.facebook.com/biogang" data-tabs="timeline" data-width="" data-height="" data-small-header="false" data-adapt-container-width="true" data-hide-cover="false" data-show-facepile="true">
                                    <blockquote cite="https://www.facebook.com/biogang" class="fb-xfbml-parse-ignore"><a href="https://www.facebook.com/biogang">BioGang.Net</a></blockquote>
                                </div>
                                <p class="menu text-left">ฐานข้อมูลสถิติ</p>
                                <button class="btn btn-success border-radius w-100 text-left mb-2">
                                    <span class="text-left ml-2">สถิติผู้เข้าชม (คน)</span>
                                    <span class="float-right"><?php echo FrontendHelper::getStatisticsPageview(); ?></span>
                                </button>
                                <button class="btn btn-success border-radius w-100 text-left mb-2">
                                    <span class="text-left ml-2">ข้อมูลความรู้วิชาการ (เรื่อง)</span>
                                    <span class="float-right"><?php echo FrontendHelper::getStatisticsKnowleage(); ?></span>
                                </button>
                                <button class="btn btn-success border-radius w-100 text-left mb-2">
                                    <span class="text-left ml-2">ข้อมูลบทความ (บทความ)</span>
                                    <span class="float-right"><?php echo FrontendHelper::getStatisticsBlog(); ?></span>
                                </button>
                                <button class="btn btn-success border-radius w-100 text-left mb-2">
                                    <span class="text-left ml-2">ข้อมูลจำนวนสมาชิก (คน)</span>
                                    <span class="float-right"><?php echo FrontendHelper::getStatisticsMemeber(); ?></span>
                                </button>
                            </div>
                        </div>

                    </div>
                    <div class="section-right col-lg-3 d-none">

                    </div>
                </div>
            </div>
        </section>
        <section class="section-word-cloud">
            <div class="container">
                <div class="row">
                    <div class="col-lg-8 d-block">
                        <p class="menu text-left text-white">Word cloud</p>
                        <div id="word-cloud-summary" class="d-none" style="width: 100%; max-height: 405px;height: 100%;background-color:#FFF"></div>
                        <div id="keyword-map-summary" class="d-none" style="width: 100%; max-height: 405px;height: 100%;background-color:#FFF"></div>
                    </div>
                    <div class="col-lg-4 mt-3">
                        <div class="row">
                            <div class="col-6">
                                <div class="word-cloud">

                                    <img src="/images/icon/Word_cloud-01.png" alt="">

                                    <p>Word cloud</p>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="keyword-map">

                                    <img src="/images/icon/Word_cloud-02.png" alt="">
                                    <p>Keyword Map</p>

                                </div>
                            </div>

                        </div>
                        <div class="row">
                            <div class="container">
                                <table class="table table-bordered table-world-cloud mt-3">
                                    <thead>
                                        <tr>
                                            <td colspan=2>Description</td>

                                        </tr>
                                    </thead>
                                    <tbody id="word-cloud-description">
                                        <tr>
                                            <td>กำลังโหลด...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                        </div>
                    </div>
                </div>
        </section>
        <section class="content">
            <div class="container">
                <p class="menu text-left  mt-4">องค์ความรู้ออนไลน์</p>
                <div class="row content-list">
                    <?php if (empty($knowledge_infographic)) { ?>
                        <div class="container empty-data">
                            <p class="text-center">ไม่พบข้อมูล</p>
                        </div>
                    <?php } ?>
                    <?php foreach ($knowledge_infographic as $key => $value) {
                        $thisUrl = '/knowledge/' . $value["id"];
                    ?>
                        <a href="/knowledge/<?= $value["id"] ?>" class="knowledge-preview col-lg-3 col-md-6 col-sm-6 col-12">
                            <object>
                                <div class="knowledge-img">
                                    <?php echo @FileLibrary::getImageFrontend(Knowledge::UPLOAD_FOLDER_KNOWLEDGE, $value["picture_path"]); ?>
                                </div>
                                <p class="title">
                                    <?php echo $value["title"]; ?>
                                </p>
                                <p class="short-desc"><?php echo strip_tags($value["description"]); ?></p>
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
        </section>
    </div>
</div>

<div class="your-item"></div>

<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myLargeModalLabel">เขียนบอร์ดใหม่</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?php
            $form = ActiveForm::begin([
                'id' => 'create-wallboard-form',
                'action' => '/wallboard/create',
                'options' => ['enctype' => 'multipart/form-data'],
                'enableClientValidation' => true
            ]);
            ?>
            <div class="modal-body">

                <?php /* echo $form->field($modelWallboard, 'description')->widget(\yii2jodit\JoditWidget::className(), [
                    'settings' => [
                        'height' => '300px',
                        'enableDragAndDropFileToEditor' => new \yii\web\JsExpression("true"),
                        'buttons' => [
                            'source', '|',
                            'bold', 'strikethrough', 'underline', 'italic', 'align', '|',
                            'ul', 'ol', 'outdent', 'indent', 'font', 'fontsize', 'brush', 'paragraph', 'eraser', '|',
                            'image', 'video', 'file', 'table', 'link', '|',
                            'align', 'undo', 'redo',
                        ],
                    ],
                    'options' => ['placeholder' => 'Wallboard'],

                ]); */ ?>

                <?= $form->field($modelWallboard, 'description')->textarea(['rows' => '6', 'class' => 'summernote']) ?>

                <?php /*    
                <?php echo froala\froalaeditor\FroalaEditorWidget::widget([
                    'model' => $modelWallboard,
                    'attribute' => 'description',
                    'options' => [
                        // html attributes
                        'key' => '112313131313',
                        'id'=>'wallboard-description'
                    ],
                    'clientOptions' => [
                        'placeholderText' => 'Wallboard',
                        'toolbarInline' => false,
                        'height' => 300,
                        'theme' => 'gray', //optional: dark, red, gray, royal
                        'language' => 'en_gb', // optional: ar, bs, cs, da, de, en_ca, en_gb, en_us ...
                        'imageUploadURL' => \yii\helpers\Url::to(['api/upload/']),
                        'toolbarButtons'   => ['fullscreen', 'bold', 'italic', 'underline', 'alignLeft', 'alignCenter', 'formatOLSimple', 'alignRight', 'paragraphFormat', 'insertImage', 'undo', 'redo']
                    ]
                ]); ?> */ ?>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">ปิด</button>
                <?= Html::submitButton("โพสต์", ['class' => "btn btn-primary"]); ?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>

