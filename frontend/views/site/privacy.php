<?php

/* @var $this yii\web\View */
use frontend\components\GoogleMapHelper;
$this->registerJsFile('@web/js/content/google-map.js', ['depends' => \yii\web\JqueryAsset::className()]);
$this->registerJsFile(GoogleMapHelper::getGoogleMapApiUrl(), ['depends' => \yii\web\JqueryAsset::className(), 'async' => true, 'defer' => true]);
$this->title = 'เงื่อนไขและนโยบายของ BIOGANG.NET';
$this->registerCss("nav {background-image: url('/images/banner/Contact_Banner.svg'); }");
use frontend\models\Banner;
$banner = Banner::find()->where(['slug_url' => 'Privacy-Policy'])->one();

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
            <li class="breadcrumb-item"><a href="/privacy"><?=$this->title?></a></li>
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
                            <p class="menu text-left">เงื่อนไขและนโยบายของ BIOGANG.NET</p>
                        </div>
                        <div class="blog-content">
                
                        
                            <p style="text-align:justify;">&nbsp; &nbsp; &nbsp; &nbsp; ยินดีต้อนรับสู่ BIO Gang
                                เว็บไซต์สําหรับแลกเปลี่ยนความรู้เกี่ยวกับทรัพยากรธรรมชาติและภูมิปัญญาท้องถิ่น
                                ในการเข้าใช้ชุมชน BIO Gang ที่ ดําเนินการบนเว็บไซต์ของเรา คุณตกลงที่จะ
                                ปฏิบัติตามเงื่อนไขการใช้บริการ
                                เหล่านี้ ("ข้อตกลง" นี้) ไม่ว่าคุณจะลงทะเบียนเป็นสมาชิกชุมชน BIO Gang ("สมาชิก") หรือไม่ก็ตาม
                                บริการ
                                ต่างๆ ของ BIO Gang ("บริการต่างๆ") ประกอบด้วย ชุมชนและเว็บไซต์</p>
                            <p style="text-align:justify;">&nbsp; &nbsp; &nbsp; &nbsp; หากคุณต้องการเป็นสมาชิก ติดต่อกับสมาชิกคนอื่น
                                และใช้ประโยชน์จาก บริการ โปรดอ่านข้อตกลงนี้และ
                                ปฏิบัติตามคําแนะนําใน กระบวนการลงทะเบียน ข้อตกลงนี้กําหนดเงื่อนไขที่มีผลทางกฎหมายสําหรับ สมาชิก
                                ภาพของคุณและ BIO Gang อาจแก้ไขข้อตกลงดังกล่าวได้เป็นครั้งคราว การแก้ไขใดๆ จะมีผลบังคับใช้เมื่อ
                                BIO Gang ได้โพสต์การแก้ไขนั้นบนเว็บไซต์แล้ว คุณอาจได้รับ สําเนาข้อตกลงฉบับนี้ทางอีเมลได้เช่นกัน</p>
                            <p style="text-align:justify;">&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;นอกจากนี้
                                เมื่อใช้ฟังก์ชันและคุณลักษณะพิเศษที่เป็นส่วนหนึ่ง ของบริการ คุณอาจต้องปฏิบัติตามข้อชี้แนะ
                                เงื่อนไข หรือกฎข้อบังคับเพิ่มเติมที่สามารถนำมาบังคับใช้กับฟังก์ชันและคุณลักษณะดังกล่าว
                                ("เงื่อนไขเพิ่มเติม") ซึ่งอาจมีการโพสต์ไว้เป็นครั้งคราว เงื่อนไขเพิ่มเติมดังกล่าวทั้งหมดของ BIO
                                Gang
                                และนโยบายความเป็นส่วนตัวของ BIO Gang ถูกนำมารวมไว้ในที่นี้โดยการอ้างอิงถึงข้อตกลงนี้</p>
                    
                            

                        </div>
                    </div>

                </div>
            </div>
        </section>
    </div>
</div>