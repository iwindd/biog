<?php

use common\components\_;
use yii\helpers\ArrayHelper;
use yii\bootstrap4\ActiveForm;
use frontend\components\GoogleMapHelper;

$this->title = $text->mainTitle;

$this->registerJsFile('@web/js/content/google-map.js', ['depends' => \yii\web\JqueryAsset::className()]);
$this->registerJsFile(GoogleMapHelper::getGoogleMapApiUrl(), ['depends' => \yii\web\JqueryAsset::className(), 'async' => true, 'defer' => true]);
$this->registerJsFile('@web/js/content/content.js', ['depends' => \yii\web\JqueryAsset::className()]);
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
            <li class="breadcrumb-item home"><a href="/">Home</a></li>
            <li class="breadcrumb-item"><a href="/content/views/student">การนำเข้าข้อมูลของฉัน</a></li>
            <li class="breadcrumb-item"><a href="#"><?= $this->title ?></a></li>
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

            <?php if (_::hasFlash('CREATE_CONTENT_SUCCESS')) : ?>
                <?php if(!empty(_::getFlash('CREATE_CONTENT_SUCCESS', 'message'))): ?>
                <?=
                    \yii\bootstrap4\Alert::widget([
                        'body' => _::getFlash('CREATE_CONTENT_SUCCESS', 'message'),
                        'options' => _::getFlash('CREATE_CONTENT_SUCCESS', 'options'),
                    ])
                ?>
                <?php endif; ?>
            <?php endif; ?>

            <?php if (_::hasFlash('CREATE_CONTENT_ERROR')) : ?>
                <?=
                    \yii\bootstrap4\Alert::widget([
                        'body' => _::getFlash('CREATE_CONTENT_ERROR', 'title') .
                            _::getFlash('CREATE_CONTENT_ERROR', 'message'),
                        'options' => _::getFlash('CREATE_CONTENT_ERROR', 'options'),
                    ])
                ?>
            <?php endif; ?>


            <?php
            $form = ActiveForm::begin([
                'id' => 'create-content-form',
                'options' => ['enctype' => 'multipart/form-data'],
                'enableClientValidation' => true
            ]);
            ?>

            <div class="row mb-4">
                <div class="col-12 mb-3">
                    <span class="h4"><?= $text->mainTitle; ?></span>
                </div>
                <div class="col-12">
                    <span class="h5 text-green-1">เลือกประเภทการนำเข้าข้อมูล</span>
                </div>
            </div>

            <?php echo $this->render('_form-' . $pageType, [
                'form' => $form,
                'model' => $model,
                'data' => $data,
                'actionType' => $actionType,
                'pageType' => $pageType,
                'pageList' => $pageList,
                'text' => $text
            ]); ?>

            <input type="hidden" id="page-type" value="<?php echo $pageType; ?>" >

            <?php ActiveForm::end(); ?>
        </div>



    </div>

</div>