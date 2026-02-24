<?php

use common\components\_;
use yii\bootstrap4\ActiveForm;
use frontend\components\GoogleMapHelper;

$this->title = "อนุมัติข้อมูลของนักเรียน";

$this->registerJsFile('@web/js/content/google-map-teacher.js', ['depends' => \yii\web\JqueryAsset::className()]);
$this->registerJsFile(GoogleMapHelper::getGoogleMapApiUrl(), ['depends' => \yii\web\JqueryAsset::className(), 'async' => true, 'defer' => true]);
use frontend\models\Banner;

$banner = Banner::find()->where(['slug_url' => 'Manage Content'])->one();

$backgroundImage = '/images/banner/Upload_Banner.png';

if(!empty($banner->picture_path)){
    $backgroundImage = '/files/banner/'.$banner->picture_path;
    $this->registerCss("nav {background-image: url('" . $backgroundImage . "'); }");
}else{
    $this->registerCss("nav {background-image: url('" . $backgroundImage . "'); }");
}

?>
<?php $this->beginBlock('banner') ?>
<div class="section-banner">
    <img src="<?= $backgroundImage; ?>" class="banner">
</div>
<?php $this->endBlock() ?>

<div class="main-breadcrumb" aria-label="breadcrumb">
    <div class="container">
        <ol class="breadcrumb pl-0">
            <li class="breadcrumb-item home"><a href="#">Home</a></li>
            <li class="breadcrumb-item"><a href="/content/views/teacher">อนุมัติข้อมูลของนักเรียน</a></li>
            <li class="breadcrumb-item"><a href="#">การนำเข้าข้อมูลของนักเรียน</a></li>
        </ol>
    </div>
</div>


<div class="container create-content-container">

    <div class="d-flex flex-column flex-md-row">

        <div class="order-0">
            <div class="menu-sidebar">
                <p class="menu"></p>
                <?php echo $this->render('@frontend/views/layouts/sidebar'); ?>
            </div>
        </div>


        <div class="order-1 flex-fill create-content-form">
            <?php
            $form = ActiveForm::begin([
                'id' => 'create-content-form',
                // 'enableClientValidation' => false
            ]);
            ?>

            <div class="row mb-4">
                <div class="col-12 mb-3">
                    <span class="h5">การนำเข้าข้อมูลของนักเรียน</span>
                </div>
                <div class="col-12">
                    <span class="h6 text-green-1">ประเภทการนำเข้าข้อมูล</span>
                </div>
            </div>

            <?php echo $this->render('_form-' . $pageType."_approve", [
                'pageType' => $pageType,
                'pageList' => $pageList,
                'form' => $form,
                'model' => $model,
            ]); ?>

            <?php ActiveForm::end(); ?>
        </div>



    </div>

</div>