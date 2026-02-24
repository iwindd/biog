<?php

/* @var $this yii\web\View */
use backend\models\Variables;
use frontend\components\GoogleMapHelper;
$this->registerJsFile('@web/js/google-map-contact.js', ['depends' => \yii\web\JqueryAsset::className()]);
$this->registerJsFile(GoogleMapHelper::getGoogleMapApiUrl(), ['depends' => \yii\web\JqueryAsset::className(), 'async' => true, 'defer' => true]);
$this->title = 'ติดต่อ BioGang';
$this->registerCss("nav {background-image: url('/images/banner/Contact_Banner.svg'); }");
use frontend\models\Banner;
$banner = Banner::find()->where(['slug_url' => 'Contact'])->one();


$phone_info = Variables::find()->where(['key' => 'phone_info'])->one(); 
$email_info = Variables::find()->where(['key' => 'email_info'])->one(); 

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
            <li class="breadcrumb-item"><a href="/contact"><?=$this->title?></a></li>
        </ol>
    </div>
</div>


<div class="site-contact" style="min-height: calc(100vh - 204px);">
    <div class="body-content">
        <section class="content pt-4">
            <div class="container">
                <div class="row">
                    <div class="menu-sidebar">
                        <p class="menu">เมนู</p>
                        <?php echo $this->render('../layouts/sidebar');?>
                    </div>
                    <div class="section-main">
                        <div class="d-flex">
                            <p class="menu text-left">ติดต่อ BioGang</p>
                        </div>
                        <div class="blog-content">
                            <div class="row">
                                <div class="container">
                                    <div class="row">
                                        <div class="col-md-9 col-sm-12" >
                                            <p class="text-green mb-2" style="font-size: 1.1rem">โครงการสื่อสารแลกเปลี่ยนข้อมูลความหลากหลายทางชีวภาพ และภูมิปัญญาท้องถิ่น</p>
                                            <p class="mb-1">สำนักงานพัฒนาเศรษฐกิจจากฐานชีวภาพ (องค์การมหาชน)</p>
                                            <p class="text-gray mb-1">ศูนย์ราชการเฉลิมพระเกียรติ ๘๐ พรรษา ๕ ธันวาคม ๒๕๕๐ อาคารรัฐประศาสนภักดี ชั้น 9 เลขที่ 120 หมู่ที่ 3 ถนนแจ้งวัฒนะ แขวงทุ่งสองห้อง เขตหลักสี่ กรุงเทพฯ 10210</p>
                                            <p class="text-gray mb-1">ข้อมูลการติดต่อ : <?php echo !empty($phone_info['value'])? $phone_info['value']:''; ?></p>
                                            <p class="text-gray mb-1 mb-3">ลิงก์โครงการ : <a href="http://www.biogang.net/" class="text-gray">http://www.biogang.net/ </a></p>
                                        </div>
                                        <div class="col-md-3 col-sm-12 text-right mb-5">
                                            <img src="/images/line.png" width="100%" >
                                            <p class="text-gray mb-1 text-left"> Line ID: @biogang </p>
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col">
                                   
                                    <input id="lat" type="hidden" value="13.880006">
                                    <input id="lng" type="hidden" value="100.565017">
                                    <div id="content-google-map" class="content-google-map" style="height:500px;border:solid #747474 1px;"></div>
                                </div>
                            </div>
                            
                           
                        </div>
                    </div>
                    
                </div>
            </div>
        </section>
    </div>
</div>