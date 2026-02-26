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

                        <?php 
                            $data_protection_pdf = Variables::find()->where(['key' => 'data_protection_pdf'])->one();
                            if (!empty($data_protection_pdf) && !empty($data_protection_pdf['value'])):
                        ?>
                        <div class="pdf-section" style="margin-top: 30px;">
                            <h4 style="margin-bottom: 15px;"><i class="fa fa-file-pdf-o" style="color: #e74c3c;"></i> เอกสาร PDF</h4>
                            <div style="margin-bottom: 15px;">
                                <a href="<?= $data_protection_pdf['value'] ?>" target="_blank" class="btn btn-primary">
                                    <i class="fa fa-download"></i> ดาวน์โหลดเอกสาร PDF
                                </a>
                            </div>
                            <div style="border: 1px solid #ddd; border-radius: 4px; overflow: hidden;">
                                <iframe src="<?= $data_protection_pdf['value'] ?>" width="100%" height="600px" style="border: none;" title="เอกสาร PDF คุ้มครองข้อมูลส่วนบุคคล"></iframe>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                </div>
            </div>
        </section>
    </div>
</div>