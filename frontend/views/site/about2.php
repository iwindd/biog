<?php

/* @var $this yii\web\View */
use frontend\components\GoogleMapHelper;
$this->registerJsFile('@web/js/content/google-map.js', ['depends' => \yii\web\JqueryAsset::className()]);
$this->registerJsFile(GoogleMapHelper::getGoogleMapApiUrl(), ['depends' => \yii\web\JqueryAsset::className(), 'async' => true, 'defer' => true]);
$this->title = 'เกี่ยวกับเรา';
$this->registerCss("nav {background-image: url('/images/banner/Contact_Banner.svg'); }");
use frontend\models\Banner;
$banner = Banner::find()->where(['slug_url' => 'About'])->one();

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
            <li class="breadcrumb-item"><a href="/about"><?=$this->title?></a></li>
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
                            <p class="menu text-left">เกี่ยวกับเรา Demo</p>
                        </div>
                        <div class="blog-content">
                
                            <p><span
                                    style="color:rgb(0,0,0);font-style:normal;font-weight:400;letter-spacing:normal;text-indent:0px;text-transform:none;white-space:normal;word-spacing:0px;background-color:rgb(255,255,255);float:none;">ด้วยสำนักงานพัฒนาเศรษฐกิจจากฐานชีวภาพ
                                    (องค์การมหาชน) หรือ สพภ.
                                    มีหน้าที่เป็นองค์กรกลางในการส่งเสริมการพัฒนาเศรษฐกิจจากฐานชีวภาพโดยการนำทรัพยากรความหลากหลายทางชีวภาพและภูมิปัญญาของชุมชนและท้องถิ่นมาประยุกต์กับวิทยาการใหม่ๆ
                                    เพื่อสร้างประโยชน์เชิงเศรษฐกิจอย่างยั่งยืน</span><br
                                    style="color:rgb(0,0,0);font-style:normal;font-weight:400;letter-spacing:normal;text-indent:0px;text-transform:none;white-space:normal;word-spacing:0px;"><br
                                    style="color:rgb(0,0,0);font-style:normal;font-weight:400;letter-spacing:normal;text-indent:0px;text-transform:none;white-space:normal;word-spacing:0px;"><span
                                    style="color:rgb(0,0,0);font-style:normal;font-weight:400;letter-spacing:normal;text-indent:0px;text-transform:none;white-space:normal;word-spacing:0px;background-color:rgb(255,255,255);float:none;">ในการนี้
                                    สพภ.
                                    ได้เล็งเห็นถึงความสำคัญของการสำรวจและรวบรวมข้อมูลทรัพยากรความหลากหลายทางชีวภาพและภูมิปัญญาท้องถิ่นในระดับประเทศโดยการมีส่วนร่วมของชุมชน
                                    จึงเห็นสมควรที่จะสร้างให้เกิดเป็นเครือข่ายการติดต่อสื่อสารแลกเปลี่ยนข้อมูลความหลากหลายทางชีวภาพและภูมิปัญญาท้องถิ่น
                                    (Communication Network) ขึ้น
                                    โดยให้เยาวชนที่อยู่ในชุมชนทำหน้าที่เป็นสื่อกลางในการรวบรวมและบันทึกข้อมูลทรัพยากรชีวภาพและภูมิปัญญาท้องถิ่นผ่านทางเครือข่าย
                                    Internet</span><br
                                    style="color:rgb(0,0,0);font-style:normal;font-weight:400;letter-spacing:normal;text-indent:0px;text-transform:none;white-space:normal;word-spacing:0px;"><br
                                    style="color:rgb(0,0,0);font-style:normal;font-weight:400;letter-spacing:normal;text-indent:0px;text-transform:none;white-space:normal;word-spacing:0px;"><span
                                    style="color:rgb(0,0,0);font-style:normal;font-weight:400;letter-spacing:normal;text-indent:0px;text-transform:none;white-space:normal;word-spacing:0px;background-color:rgb(255,255,255);float:none;">โครงการ
                                    Bio Gang และเว็บไซต์ชื่อ http://www.biogang.net
                                    จึงได้ถูกจัดทำขึ้นเพื่อให้เยาวชนใช้เป็นเครื่องมือในการติดต่อสื่อสารและบันทึกข้อมูลความหลากหลายทางชีวภาพและภูมิปัญญาท้องถิ่น
                                    ซึ่ง สพภ.
                                    จะนำข้อมูลที่ได้มาใช้เป็นส่วนหนึ่งในการบริหารจัดการการนำทรัพยากรชีวภาพและภูมิปัญญาท้องถิ่นเหล่านี้มาใช้ให้เกิดประโยชน์ในทางเศรษฐกิจให้มากที่สุด
                                    ควบคู่ไปกับการอนุรักษ์ทรัพยากรชีวภาพอย่างยั่งยืน เพื่อให้เกิดกระบวนการสร้างงาน
                                    สร้างรายได้ สร้างโอกาสให้แก่ประชาชนและชุมชนในระยะยาว</span><br
                                    style="color:rgb(0,0,0);font-style:normal;font-weight:400;letter-spacing:normal;text-indent:0px;text-transform:none;white-space:normal;word-spacing:0px;"><span
                                    style="color:rgb(0,0,0);font-style:normal;font-weight:400;letter-spacing:normal;text-indent:0px;text-transform:none;white-space:normal;word-spacing:0px;background-color:rgb(255,255,255);float:none;">โดยเยาวชนที่เป็นสมาชิก
                                    Bio Gang จะร่วมปฏิบัติตามบัญญัติ 5 ประการของ Bio Gang ซึ่งประกอบด้วย</span><br
                                    style="color:rgb(0,0,0);font-style:normal;font-weight:400;letter-spacing:normal;text-indent:0px;text-transform:none;white-space:normal;word-spacing:0px;">
                            </p>
                            <ol
                                style="color:rgb(0,0,0);font-style:normal;font-weight:400;letter-spacing:normal;text-indent:0px;text-transform:none;white-space:normal;word-spacing:0px;">
                                <li>ช่วยบันทึกข้อมูลทรัพยากรชีวภาพ ผลิตภัณฑ์ และสถานที่ท่องเที่ยวเชิงนิเวศของชุมชน
                                </li>
                                <li>ทำการค้นหาผู้รู้ในชุมชนและบันทึกภูมิปัญญาท้องถิ่นของผู้รู้นั้น</li>
                                <li>ชวนเพื่อนมาร่วมเป็นสมาชิก Bio Gang (Member get Member)</li>
                                <li>พูดคุย แลกเปลี่ยนความรู้กับเพื่อนต่างถิ่น 4 ภาค</li>
                                <li>นำความรู้ที่ได้จากการแลกเปลี่ยนกับเพื่อนสมาชิกกลับคืนสู่ชุมชนของตนเอง</li>
                            </ol>
                            <p style="text-align:center;"><img src="/images/biogang5.jpg"
                                    alt="บัญญัติ 5 ประการของ Bio Gang"
                                    style="color:rgb(0,0,0);font-style:normal;font-weight:400;letter-spacing:normal;text-indent:0px;text-transform:none;white-space:normal;word-spacing:0px;">
                            </p>
                            

                        </div>
                    </div>

                </div>
            </div>
        </section>
    </div>
</div>