<?php

/* @var $this yii\web\View */
use backend\models\Variables;
use frontend\components\GoogleMapHelper;
$this->registerJsFile('@web/js/content/google-map.js', ['depends' => \yii\web\JqueryAsset::className()]);
$this->registerJsFile(GoogleMapHelper::getGoogleMapApiUrl(), ['depends' => \yii\web\JqueryAsset::className(), 'async' => true, 'defer' => true]);
$this->title = 'เงื่อนไขและนโยบายคุ้มครองข้อมูลส่วนบุลคล';
$this->registerCss("nav {background-image: url('/images/banner/Contact_Banner.svg'); }");
use frontend\models\Banner;
$banner = Banner::find()->where(['slug_url' => 'Protection-Policy'])->one();

?>
<?php $this->beginBlock('banner') ?>
<div class="section-banner">
    <?php if(!empty($banner->picture_path)): ?>
        <img src="/files/banner/<?php echo $banner->picture_path; ?>" class="banner injectable">
    <?php else: ?>
        <img src="/images/banner/Contact_Banner.png" class="banner injectable">
    <?php endif; ?>
</div>
<?php $this->endBlock() ?>

<div class="main-breadcrumb" aria-label="breadcrumb">
    <div class="container">
        <ol class="breadcrumb pl-0">
            <li class="breadcrumb-item home"><a href="/">Home</a></li>
            <li class="breadcrumb-item"><a href="/protection"><?=$this->title?></a></li>
        </ol>
    </div>
</div>


<div class="site-contact" style="min-height: calc(100vh - 204px);">
    <div class="body-content">
        <section class="content pt-4">
            <div class="container">
                <div class="row">
                    <div class="menu-sidebar">
                        <p class="menu"></p>
                        <?php echo $this->render('../layouts/sidebar');?>
                    </div>
                    <div class="section-main">
                        <div class="d-flex">
                            <p class="menu text-left">เงื่อนไขและนโยบายคุ้มครองข้อมูลส่วนบุคคล</p>
                        </div>
                        <div class="blog-content fr-view">

                            <?php $data_protection = Variables::find()->where(['key' => 'data_protection'])->one(); 
                                if(!empty($data_protection)){
                                    echo $data_protection['value'];
                                }
                            ?>
                            
                        </div>
                    </div>

                </div>
            </div>
        </section>
    </div>
</div>