<?php

use frontend\components\Utils;
use frontend\models\District;
use frontend\models\Province;
use frontend\models\Region;
use frontend\models\School;
use frontend\models\Subdistrict;
use frontend\models\Zipcode;
use kartik\date\DatePicker;
use kartik\select2\Select2;
use yii\bootstrap4\ActiveForm;
use yii\bootstrap4\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

$region = ArrayHelper::map(Region::find()->all(), 'id', 'name_th');
$province = ArrayHelper::map(Province::find()->all(), 'id', 'name_th');
$district = array();  // ArrayHelper::map(District::find()->all(), 'id', 'name_th');
$subdistrict = array();  // ArrayHelper::map(Subdistrict::find()->all(), 'id', 'name_th');
$zipcode = array();  // ArrayHelper::map(Zipcode::find()->all(), 'id', 'name_th');
$dataSchool = ArrayHelper::map(School::find()->all(), 'id', 'name');

$this->registerJsFile(Url::base() . '/js/location.js', ['depends' => [\frontend\assets\AppAsset::className()]]);
$this->registerJsFile(Url::base() . '/js/image-preview.js', ['depends' => [\frontend\assets\AppAsset::className()]]);
$this->registerJsFile(Url::base() . '/js/user-profile.js', ['depends' => [\frontend\assets\AppAsset::className()]]);

if (!empty($_GET['invite'])) {
    $profileModel->invite_friend = base64_decode($_GET['invite']);
}

$this->title = Yii::t('app', 'สมัครสมาชิก');

use frontend\models\Banner;

$banner = Banner::find()->where(['slug_url' => 'Register'])->one();

$backgroundImage = '/images/banner/Register_Banner.png';

if (!empty($banner->picture_path)) {
    $backgroundImage = '/files/banner/' . $banner->picture_path;
    $this->registerCss("nav {background-image: url('" . $backgroundImage . "'); }");
} else {
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
            <li class="breadcrumb-item"><a href="#"><?= $this->title ?></a></li>
        </ol>
    </div>
</div>

<div id="content_container" class="container register-container">

    <div class="register-form row ">
        <?php $form = ActiveForm::begin([
            'options' => [
                'enctype' => 'multipart/form-data',
            ]
        ]); ?>
        <div class="col">

            <div class="row">
                <div class="col-md-6 col-sm-12 d-flex justify-content-between">
                    <div class="">
                        <span class="h6">ลงทะเบียนสมาชิก</span>
                    </div>

                    <div class="">
                        <div class="text-center">
                            <span class="h6 d-block">มีบัญชีผู้ใช้แล้ว</span>
                            <a class="btn btn-primary btn-login" href="/user/login">เข้าสู่ระบบได้ ที่นี่</a>
                        </div>
                    </div>
                </div>

            </div>

            <div class="row mb-3">

                <div class="col-md-6 col-sm-12">

                    <?= $form
                        ->field($userModel, 'email')
                        ->textInput()
                        ->input('text', [
                            'placeholder' => 'อีเมล'
                        ])->label('อีเมล *'); ?>

                    <div class="form-group ">
                        <?= $form
                            ->field($userModel, 'new_password')
                            ->textInput()
                            ->input('password', [
                                'placeholder' => 'รหัสผ่าน'
                            ])->label('รหัสผ่าน *'); ?>
                    </div>
                    <div class="form-group ">
                        <?= $form
                            ->field($userModel, 'confirm_password')
                            ->textInput()
                            ->input('password', [
                                'placeholder' => 'ยืนยันรหัสผ่าน'
                            ])->label('ยืนยันรหัสผ่าน *'); ?>
                    </div>

                </div>

            </div>

            <div class="row">
                <?php
                if (!empty($case_error)) {
                    foreach ($case_error as $error) {
                        ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error; ?>

                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <?php
                    }
                }
                ?>
            </div>

            <div class="row ">



                <div class="col-lg-6 col-md-12 col-border -left">

                    <div class="mb-4">
                        <span class="h6">ข้อมูลส่วนตัว</span>
                    </div>

                    <?= $form->field($profileModel, 'display_name')->textInput()->input('text', ['placeholder' => 'ชื่อที่ใช้แสดง'])->label('ชื่อที่ใช้แสดง *'); ?>
                    <?= $form->field($profileModel, 'firstname')->textInput()->input('text', ['placeholder' => 'ชื่อ'])->label('ชื่อ *'); ?>
                    <?= $form->field($profileModel, 'lastname')->textInput()->input('text', ['placeholder' => 'นามสกุล'])->label('นามสกุล *'); ?>
                    <?= $form->field($profileModel, 'phone')->textInput()->input('text', ['placeholder' => 'หมายเลขโทรศัพท์มือถือ'])->label('หมายเลขโทรศัพท์มือถือ'); ?>

                    <?php
                    $genderList = [
                        'male' => 'ชาย',
                        'female' => 'หญิง'
                    ];
                    echo $form->field($profileModel, 'gender')->dropDownList($genderList, ['prompt' => 'เพศ'])->label('เพศ');
                    ?>

                    <?php
                    echo $form->field($profileModel, 'birthdate')->widget(DatePicker::classname(), [
                        'options' => ['placeholder' => 'วัน/เดือน/ปีเกิด'],
                        'language' => 'th',
                        'pluginOptions' => [
                            'orientation' => 'top right',
                            'format' => 'dd/mm/yyyy',
                            'autoclose' => true
                        ],
                        'type' => DatePicker::TYPE_COMPONENT_APPEND,
                    ])->label('วันเกิด');
                    ?>

                    <?= $form->field($profileModel, 'invite_friend')->textInput()->input('text', ['placeholder' => 'รหัสผู้แนะนำ / อีเมลผู้แนะนำ'])->label('ได้รับคำเชิญจาก'); ?>

                    <div class="mt-4 mb-2">
                        <span class="h6">ที่อยู่</span>
                    </div>

                    <?= $form->field($profileModel, 'home_number')->textInput()->input('text', ['placeholder' => 'บ้านเลขที่/หมู่ที่-หมู่บ้าน/ซอย/ถนน'])->label('บ้านเลขที่/หมู่ที่-หมู่บ้าน/ซอย/ถนน'); ?>

                    <div class="row">
                        <div class="col">

                            <?= $form->field($profileModel, 'province_id')->dropDownList($province, ['prompt' => 'กรุณาเลือกจังหวัด'])->label('จังหวัด'); ?>

                        </div>
                        <div class="col">
                            <?= $form->field($profileModel, 'district_id')->dropDownList($district, ['prompt' => 'กรุณาเลือกอำเภอ'])->label('อำเภอ'); ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <?= $form->field($profileModel, 'subdistrict_id')->dropDownList($subdistrict, ['prompt' => 'กรุณาเลือกตำบล'])->label('ตำบล'); ?>
                        </div>
                        <div class="col">
                            <?= $form->field($profileModel, 'zipcode_id')->textInput()->input('text', ['placeholder' => 'รหัสไปรษณีย์'])->label('รหัสไปรษณีย์'); ?>
                        </div>
                    </div>

                </div>

                <div class="col-lg-6 col-md-12 col-border -right">

                    <div class="row justify-content-center mb-4">
                        <div>
                            <div>
                                <img src="/images/default-user.png"
                                    class="profile-image border border-secondary rounded-circle img-fluid">
                            </div>
                            <div class="d-flex justify-content-end">
                                <div class="wrap-btn-upload">
                                    <?= $form->field($profileModel, 'picture')->fileInput(['style' => 'display: none;'])->label(false) ?>
                                    <div
                                        class="btn-upload-profile btn btn-secondary d-flex justify-content-center align-items-center">
                                        <i class="align-self-center fas fa-camera"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="invalid-feedback image-error" style="display: block;"></div>
                    </div>

                    <div class="mb-4">
                        <span class="h6">ข้อมูลการศึกษา</span>
                    </div>

                    <?php

                    echo $form->field($schoolModel, 'school_id')->widget(Select2::classname(), [
                        'data' => $dataSchool,
                        'options' => ['placeholder' => 'ชื่อโรงเรียน/มหาวิทยาลัย'],
                        'pluginOptions' => [
                            'allowClear' => true
                        ],
                    ])->label('ชื่อโรงเรียน/มหาวิทยาลัย');

                    ?>

                    <?=
                    $form->field($userModel, 'role', ['errorOptions' => ['tag' => 'small']])->inline()->radioList([
                        'student' => 'นักเรียน/นิสิต',
                        'teacher' => 'อาจารย์',
                    ])->label(false);
                    ?>

                    <?= $form->field($profileModel, 'class')->textInput()->input('text', ['placeholder' => 'ชั้นเรียน'])->label('ชั้นเรียน'); ?>

                    <?= $form->field($profileModel, 'major')->textInput()->input('text', ['placeholder' => 'ประจำสาขา/วิชา'])->label('ประจำสาขา/วิชา'); ?>
                </div>

            </div>
        </div>


        <div class="row mt-5 justify-content-center align-items-center">

            <div class="form-group">
                <?= $form->field($userModel, 'reCaptcha')->widget(
                    \himiklab\yii2\recaptcha\ReCaptcha2::className()
                )->label(false) ?>
            </div>

        </div>

        <div class="row mt-4 justify-content-center align-items-center">

            <div class="form-group">

                <?= $form->field($userModel, 'accept_biog')->checkbox(['uncheck' => false])->label('ยอมรับ <a id="show-accept-policy-cuar" href="javascript:void(0);" data-toggle="modal" data-target="#biogangAceptModalBlock">เงื่อนไขและนโยบายของ BIOGANG.NET</a>'); ?>


                <?= $form->field($userModel, 'accept_condition', ['options' => ['class' => 'd-inline']])->checkbox(['uncheck' => false])->label('ยอมรับ <a id="show-accept-policy-personal" href="javascript:void(0);" data-toggle="modal" data-target="#conditionsModalBlock">เงื่อนไขและนโยบายคุ้มครองข้อมูลส่วนบุลคล</a>'); ?>

            </div>

        </div>




        <div class="row mt-4 justify-content-center align-items-center">

            <div class="form-group">
                <?= Html::submitButton('ลงทะเบียน', ['class' => 'btn btn-primary btn-register', 'name' => 'register-button']) ?>
            </div>
        </div>

        <?php ActiveForm::end(); ?>


    </div>
</div>

</div>

<div class="modal fade" id="biogangAceptModalBlock" tabindex="-1" role="dialog" aria-labelledby="biogangAceptModal"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="biogangAceptModal">เงื่อนไขและนโยบายของ BIOGANG.NET</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
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
            <div class="modal-footer">
                <button type="button" id="biogand-acept" class="btn btn-primary" data-dismiss="modal">ตกลง</button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="conditionsModalBlock" tabindex="-1" role="dialog" aria-labelledby="conditionsModal"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="conditionsModal">เงื่อนไขและนโยบายคุ้มครองข้อมูลส่วนบุลคล</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p style="font-weight: 500;">นโยบายการคุ้มครองข้อมูลส่วนบุคคล (Privacy Policy)</p>
                <p style="text-align:justify;">&nbsp; &nbsp; &nbsp; &nbsp; สํานักงานพัฒนาเศรษฐกิจจากฐานชีวภาพ
                    (องค์การมหาชน) ขอแนะนําให้ท่านทําความเข้าใจนโยบายส่วน
                    บุคคล (Privacy Policy) นี้ เนื่องจาก นโยบายนี้อธิบายถึงวิธีการที่สํานักงานฯ
                    ปฏิบัติต่อข้อมูลส่วนบุคคลของ
                    ท่าน เช่น การเก็บรวบรวม การจัดเก็บรักษา การใช้ การเปิดเผย รวมถึงสิทธิต่าง ๆ ของท่าน เป็นต้น เพื่อให้
                    ท่านได้รับทราบถึงนโยบายในการคุ้มครองข้อมูลส่วนบุคคลของสํานักงานฯ สํานักงานฯ จึงประกาศนโยบาย
                    ส่วนบุคคล ดังต่อไปนี้</p>

                <p style="font-weight: 500;">ข้อมูลส่วนบุคคล</p>
                <p style="text-align:justify;">&nbsp; &nbsp; &nbsp; &nbsp; “ข้อมูลส่วนบุคคล” หมายถึง
                    ข้อมูลที่สามารถระบุตัวตนของท่าน หรืออาจจะระบุตัวตนของท่านได้ ไม่ว่า
                    ทางตรงหรือทางอ้อม</p>
                <p style="font-weight: 500;">การเก็บรวบรวมข้อมูลส่วนบุคคลอย่างจํากัด</p>
                <p style="text-align:justify;">&nbsp; &nbsp; &nbsp; &nbsp;
                    &nbsp;การจัดเก็บรวบรวมข้อมูลส่วนบุคคลจะกระทําโดยมี วัตถุประสงค์ ขอบเขต และใช้วิธีการที่ชอบด้วยกฎหมาย
                    และเป็นธรรม ในการเก็บรวบรวมและจัดเก็บข้อมูล ตลอดจนเก็บรวบรวม และจัดเก็บข้อมูลส่วนบุคคลอย่าง
                    จํากัดเพียงเท่าที่จําเป็นแก่การให้บริการ
                    หรือบริการด้วยวิธีการทางอิเล็กทรอนิกส์อื่นใดภายใต้วัตถุประสงค์
                    ของสํานักงานฯ เท่านั้น <br>
                    ทั้งนี้สำนักงานฯ จะดำเนินการให้เจ้าของข้อมูล รับรู้ ให้ความยินยอม ทางอิเล็กทรอนิกส์
                    หรือตามแบบวิธีการของสำนักงานฯ สำนักงานฯ
                    อาจจัดเก็บข้อมูลส่วนบุคคลของท่านซึ่งเกี่ยวกับความสนใจและบริการที่ท่านใช้ ซึ่งอาจประกอบด้วยเรื่อง
                    ชื่อ นามสกุล เพศ อายุ สัญชาติ วันเกิด สถานภาพสมรส ที่อยู่ อาชีพ สถานที่ทำงาน รหัสไปรษณีย์
                    ที่อยู่ไปรษณีย์อิเล็กทรอนิกส์ (Email address) หมายเลขโทรศัพท์ บัตรเครดิต รายได้ งบประมาณ
                    เหตุผลในการซื้อหรือเลือกที่อยู่ วัตถุประสงค์การสืบค้นข้อมูลในเว็บไซต์ หรือข้อมูลอื่นใด
                    ที่จะเป็นประโยชน์ในการให้บริการ ทั้งนี้ การดำเนินการดังกล่าวข้างต้น สำนักงานฯ
                    จะขอความยินยอมจากท่านก่อนทำการเก็บรวบรวม เว้นแต่

                    เป็นการปฏิบัติตามกฎหมาย เช่น พระราชบัญญัติคุ้มครองข้อมูลส่วนบุคคล
                    พระราชบัญญัติว่าด้วยธุรกรรมทางอิเล็กทรอนิกส์ พระราชบัญญัติการประกอบกิจการโทรคมนาคม
                    พระราชบัญญัติป้องกันและปราบปรามการฟอกเงิน ประมวลกฎหมายแพ่งและอาญา
                    ประมวลกฎหมายวิธีพิจารณาความแพ่งและอาญา เป็นต้น
                    เป็นไปเพื่อประโยชน์แก่การสอบสวนของพนักงานสอบสวน หรือการพิจารณาพิพากษาคดีของศาล
                    เพื่อประโยชน์ของท่าน และการขอความยินยอมไม่อาจกระทำได้ในเวลานั้น
                    เป็นการจำเป็นเพื่อประโยชน์โดยชอบด้วยกฎหมายของสำนักงานฯ
                    หรือของบุคคลหรือนิติบุคคลอื่นที่ไม่ใช่สำนักงานฯ
                    เป็นการจำเป็นเพื่อป้องกันหรือระงับอันตรายต่อชีวิต ร่างกาย หรือสุขภาพของบุคคล
                    เป็นการจำเป็นเพื่อการปฏิบัติตามสัญญาซึ่งเจ้าของข้อมูลส่วนบุคคลเป็นคู่สัญญาหรือเพื่อใช้ในการดำเนินการตามคำขอของเจ้าของข้อมูลส่วนบุคคลก่อนเข้าทำสัญญานั้น
                    เพื่อให้บรรลุวัตถุประสงค์ที่เกี่ยวกับการจัดทำเอกสารประวัติศาสตร์หรือจดหมายเหตุ เพื่อประโยชน์สาธารณะ
                    หรือเพื่อการศึกษา วิจัย การจัดทำสถิติ ซึ่งได้จัดให้มีมาตรการป้องกันที่เหมาะสม

                </p>

                <p style="font-weight: 500;">มาตรการรักษาความมั่นคงปลอดภัยและคุณภาพของข้อมูล</p>
                <p style="text-align:justify;">&nbsp; &nbsp; &nbsp; &nbsp;
                    &nbsp;สำนักงานฯ ตระหนักถึงความสำคัญของการรักษาความมั่นคงปลอดภัยของข้อมูลส่วนบุคคลของท่าน สำนักงานฯ
                    จึงกำหนดให้มีมาตรการในการรักษาความมั่นคงปลอดภัยของข้อมูลส่วนบุคคลอย่างเหมาะสมและสอดคล้องกับการรักษาความลับของข้อมูลส่วนบุคคลเพื่อป้องกันการสูญหาย
                    การเข้าถึง ทำลาย ใช้ แปลง แก้ไขหรือเปิดเผยข้อมูลส่วนบุคคลโดยไม่มีสิทธิหรือโดยไม่ชอบด้วยกฎหมาย
                    ตลอดจนการป้องกันมิให้มีการนำข้อมูลส่วนบุคคลไปใช้โดยมิได้รับอนุญาต ทั้งนี้
                    เป็นไปตามที่กำหนดในนโยบายการรักษาความมั่นคงปลอดภัยไซเบอร์ ของสำนักงานฯ </p>

                <p style="text-align:justify;">&nbsp; &nbsp; &nbsp; &nbsp;

                    ในการคุ้มครองและป้องกันความเป็นส่วนตัวของข้อมูลส่วนบุคคลในการเข้าเยี่ยมชมเว็บไซต์นั้น สำนักงานฯ
                    ได้ใช้ระบบ Secure Socket Layer (SSL)
                    ซึ่งเป็นมาตรฐานการรักษาความปลอดภัยของข้อมูลที่รับส่งผ่านอินเทอร์เน็ต
                    เพื่อป้องกันผู้ที่แอบดักจับข้อมูลขณะที่มีการส่งผ่านเครือข่าย Internet และใช้ Firewall
                    เทคโนโลยีนี้ยังใช้สำหรับการยืนยันความมีอยู่จริงของเว็บไซต์อีกด้วย
                    สำนักงานฯ จะมีการปรับปรุงและทดสอบระบบเทคโนโลยีของสำนักงานฯ
                    อย่างสม่ำเสมอเพื่อให้แน่ใจว่าข้อมูลส่วนบุคคลมีความปลอดภัยสูงสุดและน่าเชื่อถือ ในการนี้ สำนักงานฯ
                    สงวนสิทธิในการเปลี่ยนแปลงเครื่องมือการรักษาความปลอดภัย สำนักงานฯ
                    เห็นว่าเครื่องมือดังกล่าวเป็นไปตามมาตรฐานความปลอดภัยของข้อมูลอยู่แล้ว </p>

                <p style="text-align:justify;">&nbsp; &nbsp; &nbsp; &nbsp;

                    ข้อมูลส่วนบุคคลของท่านที่สำนักงานฯ ได้รับมา เช่น ชื่อ อายุ ที่อยู่ หมายเลขโทรศัพท์
                    หมายเลขบัตรประชาชน ข้อมูลทางการเงิน เป็นต้น ซึ่งสามารถบ่งบอกตัวบุคคลของท่านได้
                    และเป็นข้อมูลส่วนบุคคลที่มีความถูกต้องและเป็นปัจจุบัน
                    จะถูกนำไปใช้ให้เป็นไปตามวัตถุประสงค์การดำเนินงานของสำนักงานฯ เท่านั้น และสำนักงานฯ
                    จะดำเนินมาตรการที่เหมาะสมเพื่อคุ้มครองสิทธิของเจ้าของข้อมูลส่วนบุคคล
                    มาตรการคุ้มครองและรักษาความลับของผู้ร้องเรียนเพื่อเป็นการคุ้มครองสิทธิของผู้ร้อง
                    เรียนและผู้ให้ข้อมูลที่กระทำโดยเจตนาสุจริต สำนักงานฯ จะปกปิดชื่อ ที่อยู่ หรือข้อมูลใดๆ
                    ที่สามารถระบุตัวผู้ร้องเรียนหรือผู้ให้ข้อมูลได้ และเก็บรักษาข้อมูลของ
                    ผู้ร้องเรียนและผู้ให้ข้อมูลไว้เป็นความลับ
                    โดยจำกัดเฉพาะผู้รับผิดชอบในการดำเนินการตรวจสอบเรื่องร้องเรียนเท่านั้นที่จะเข้าถึงข้อมูลดังกล่าวได้
                </p>


                <p style="font-weight: 500;">วัตถุประสงค์ในการรวบรวม จัดเก็บ ใช้ ข้อมูลส่วนบุคคล</p>
                <p style="text-align:justify;">&nbsp; &nbsp; &nbsp; &nbsp;
                    &nbsp;สำนักงานฯ รวบรวม จัดเก็บ ใช้ ข้อมูลส่วนบุคคลของท่าน เพื่อประโยชน์ในการให้บริการแก่ท่าน
                    รวมถึงบริการที่ท่านสนใจ หรือการจัดทำบริการทางดิจิทัล หรือการวิจัยตลาดและการจัดกิจกรรมส่งเสริมการขาย
                    หรือเพื่อประโยชน์ในการจัดทำฐานข้อมูลและใช้ข้อมูลเพื่อเสนอสิทธิประโยชน์ตามความสนใจของท่าน
                    หรือเพื่อประโยชน์ในการวิเคราะห์และนำเสนอบริการหรือผลิตภัณฑ์ใดๆ ของผู้ให้บริการ
                    และ/หรือบุคคลที่เป็นผู้จำหน่าย เป็นตัวแทน หรือมีความเกี่ยวข้องกับผู้ให้บริการ และ/หรือของบุคคลอื่น
                    และเพื่อวัตถุประสงค์อื่นใดที่ไม่ต้องห้ามตามกฎหมาย
                    และ/หรือเพื่อปฏิบัติตามกฎหมายหรือกฎระเบียบที่ใช้บังคับกับผู้ให้บริการ ทั้งขณะนี้และภายภาคหน้า
                    รวมทั้งยินยอมให้ผู้ให้บริการส่ง โอน และ/หรือเปิดเผยข้อมูลส่วนบุคคลให้แก่สำนักงานฯ
                    กลุ่มธุรกิจของผู้ให้บริการ พันธมิตรทางธุรกิจ ผู้ให้บริการภายนอก ผู้ประมวลผลข้อมูล
                    ผู้สนใจจะเข้ารับโอนสิทธิ ผู้รับโอนสิทธิ หน่วยงาน/องค์กร/นิติบุคคลใดๆ
                    ที่มีสัญญาอยู่กับผู้ให้บริการหรือมีความสัมพันธ์ด้วย และ/หรือผู้ให้บริการคลาวด์คอมพิวติ้ง
                    โดยยินยอมให้ผู้ให้บริการ ส่ง โอน และ/หรือเปิดเผยข้อมูลดังกล่าวได้ ทั้งในประเทศและต่างประเทศ
                    และสำนักงานฯ
                    จะจัดเก็บรักษาข้อมูลดังกล่าวไว้ตามระยะเวลาเท่าที่จำเป็นสำหรับวัตถุประสงค์เหล่านั้นเท่านั้นหากภายหลังมีการเปลี่ยนแปลงวัตถุประสงค์ในการเก็บรวบรวมข้อมูลส่วนบุคคล
                    สำนักงานฯ จะประกาศให้ท่านทราบ </p>

                <p style="font-weight: 500;">ข้อจำกัดในการใช้และ/หรือเปิดเผยข้อมูลส่วนบุคคล</p>
                <p style="text-align:justify;">&nbsp; &nbsp; &nbsp; &nbsp;
                    &nbsp;สำนักงานฯ จะใช้ เปิดเผยข้อมูลส่วนบุคคลของท่านได้
                    ตามความยินยอมของท่านโดยจะต้องเป็นการใช้ตามวัตถุประสงค์ของการเก็บรวบรวม จัดเก็บ ข้อมูลของสำนักงานฯ
                    เท่านั้น สำนักงานฯ จะกำกับดูแลพนักงาน เจ้าหน้าที่หรือผู้ปฏิบัติงานของสำนักงานฯ
                    มิให้ใช้และ/หรือเปิดเผย
                    ข้อมูลส่วนบุคคลของท่านนอกเหนือไปจากวัตถุประสงค์ของการเก็บรวบรวมข้อมูลส่วนบุคคลหรือเปิดเผยต่อบุคคลภายนอก
                    เว้นแต่
                    เป็นการปฏิบัติตามกฎหมาย เช่น พระราชบัญญัติคุ้มครองข้อมูลส่วนบุคคล
                    พระราชบัญญัติว่าด้วยธุรกรรมทางอิเล็กทรอนิกส์ พระราชบัญญัติป้องกันและปราบปรามการฟอกเงิน
                    ประมวลกฎหมายแพ่งและอาญา ประมวลกฎหมายวิธีพิจารณาความแพ่งและอาญา เป็นต้น
                    เป็นการปฏิบัติตามกฎหมาย เช่น พระราชบัญญัติคุ้มครองข้อมูลส่วนบุคคล
                    พระราชบัญญัติว่าด้วยธุรกรรมทางอิเล็กทรอนิกส์ พระราชบัญญัติป้องกันและปราบปรามการฟอกเงิน
                    ประมวลกฎหมายแพ่งและอาญา ประมวลกฎหมายวิธีพิจารณาความแพ่งและอาญา เป็นต้น
                    เป็นไปเพื่อประโยชน์แก่การสอบสวนของพนักงานสอบสวน หรือการพิจารณาพิพากษาคดีของศาล
                    เพื่อประโยชน์ของท่าน และการขอความยินยอมไม่อาจกระทำได้ในเวลานั้น
                    เป็นการจำเป็นเพื่อประโยชน์โดยชอบด้วยกฎหมายของสำนักงานฯ
                    หรือของบุคคลหรือนิติบุคคลอื่นที่ไม่ใช่สำนักงานฯ
                    เป็นการจำเป็นเพื่อป้องกันหรือระงับอันตรายต่อชีวิต ร่างกาย หรือสุขภาพของบุคคล
                    เป็นการจำเป็นเพื่อการปฏิบัติตามสัญญาซึ่งเจ้าของข้อมูลส่วนบุคคลเป็นคู่สัญญาหรือเพื่อใช้ในการดำเนินการตามคำขอของเจ้าของข้อมูลส่วนบุคคลก่อนเข้าทำสัญญานั้น
                    เพื่อให้บรรลุวัตถุประสงค์ที่เกี่ยวกับการจัดทำเอกสารประวัติศาสตร์หรือจดหมายเหตุ เพื่อประโยชน์สาธารณะ
                    หรือเพื่อการศึกษา วิจัย การจัดทำสถิติ ซึ่งได้จัดให้มีมาตรการป้องกันที่เหมาะสม
                    สำนักงานฯ
                    อาจใช้บริการสารสนเทศของผู้ให้บริการซึ่งเป็นบุคคลภายนอกเพื่อให้ดำเนินการเก็บรักษาข้อมูลส่วนบุคคล
                    ซึ่งผู้ให้บริการนั้นจะต้องมีมาตรการรักษาความมั่นคงปลอดภัย โดยห้ามดำเนินการเก็บรวบรวม
                    ใช้หรือเปิดเผยข้อมูลส่วนบุคคลนอกเหนือจากที่สำนักงานฯ กำหนด
                </p>

                <p style="font-weight: 500;">การวิเคราะห์ข้อมูลและการใช้คุ้กกี้ (Cookies)</p>
                <p style="text-align:justify;">&nbsp; &nbsp; &nbsp; &nbsp;
                    &nbsp;สำนักงานฯ อาจรวบรวมข้อมูลส่วนบุคคลของลูกค้า ทั้งนี้
                    รวมถึงแต่ไม่จำกัดเฉพาะข้อมูลที่ได้มาหรือเกี่ยวข้องกับกิจกรรมออนไลน์ของลูกค้าบนเว็บไซต์และบนอุปกรณ์อื่นใดที่ถูกเชื่อมต่อและเข้าใช้งานของลูกค้า
                    ในการนี้ สำนักงานฯ อาจใช้เทคโนโลยีหรือเครื่องมือวิเคราะห์จาก Cookies หรือบริการของบุคคลภายนอก
                    เพื่อประโยชน์ในการพัฒนาผลิตภัณฑ์และการให้บริการที่เกี่ยวเนื่องกับผลิตภัณฑ์ของสำนักงานฯ
                </p>
                <p style="text-align:justify;">&nbsp; &nbsp; &nbsp; &nbsp;
                    &nbsp;คุ้กกี้ (Cookies) คือ ไฟล์ขนาดเล็กที่ใช้เพื่อจัดเก็บข้อมูลบนคอมพิวเตอร์ แท็บเล็ต
                    หรืออุปกรณ์อิเล็กทรอนิกส์อื่น ๆ ของเจ้าของข้อมูล เพื่อเก็บข้อมูลในการเข้าเยี่ยมชม หรือใช้งานเว็บไซต์
                    โดยประเมินผู้เข้าชมและตรวจสอบความถี่ในการเข้าชมเว็บไซต์เพื่อนำมาปรับปรุงการให้ข้อมูลและบริการที่ดียิ่งขึ้น
                    อีกทั้งช่วยอำนวยความสะดวกให้แก่ลูกค้าในการไม่ต้องกรอกข้อมูลประจำตัวทุกครั้งที่มีการลงชื่อเข้าใช้งาน
                </p>
                <p style="text-align:justify;">&nbsp; &nbsp; &nbsp; &nbsp;
                    &nbsp;เจ้าของข้อมูลสามารถปรับแต่งเบราว์เซอร์เพื่อยอมรับคุกกี้ทั้งหมด ปฏิเสธคุกกี้ทั้งหมด
                    หรือแจ้งเตือนให้เจ้าของข้อมูลทราบเมื่อมีการส่งคุ้กกี้
                    อย่างไรก็ตามการปิดใช้งานคุ้กกี้อาจจะส่งผลต่อการใช้บริการเหล่านั้นทั้งหมดหรือบางส่วน อนึ่ง สำนักงานฯ
                    อาจใช้เทคโนโลยี Flash Cookie ซึ่งอาจทำให้ลูกค้าไม่สามารถเปลี่ยนแปลงการตั้งค่าได้
                </p>
                <p style="font-weight: 500;">สิทธิเกี่ยวกับข้อมูลส่วนบุคคลของท่าน</p>
                <p style="text-align:justify;">ท่านสามารถขอเข้าถึง ขอรับสำเนาข้อมูลส่วนบุคคลของท่าน
                    ตามหลักเกณฑ์และวิธีการที่สำนักงานฯ กำหนด หรือขอให้เปิดเผยการได้มาซึ่งข้อมูลส่วนบุคคล ทั้งนี้
                    สำนักงานฯ อาจปฏิเสธคำขอของท่านได้ตามที่กฎหมายกำหนดหรือตามคำสั่งศาล <br>
                    ท่านสามารถขอแก้ไขหรือเปลี่ยนแปลงข้อมูลส่วนบุคคลของท่านที่ไม่ถูกต้องหรือไม่สมบูรณ์
                    และทำให้ข้อมูลของท่านเป็นปัจจุบันได้ <br>
                    ท่านสามารถขอลบหรือทำลายข้อมูลส่วนบุคคลท่าน เว้นแต่เป็นกรณีที่สำนักงานฯ
                    ต้องปฏิบัติตามกฎหมายที่เกี่ยวข้องในการเก็บรักษาข้อมูลดังกล่าว

                </p>

                <p style="font-weight: 500;">การเปิดเผยเกี่ยวกับการดำเนินการ แนวปฏิบัติ
                    และนโยบายที่เกี่ยวกับข้อมูลส่วนบุคคล</p>
                <p style="text-align:justify;">&nbsp; &nbsp; &nbsp; &nbsp;
                    &nbsp;สำนักงานฯ จะเปิดเผยข้อมูลส่วนบุคคลของลูกค้าเป็นการภายใน เฉพาะแก่ผู้ที่มีหน้าที่ใช้ข้อมูล
                    เพื่อการช่วยเหลือ หรือเพื่อให้บริการตามคำขอของลูกค้า อย่างไรก็ดี อาจมีกรณีที่สำนักงานฯ
                    จำเป็นต้องเปิดเผยข้อมูลส่วนบุคคลของลูกค้า ให้แก่บุคคลที่สาม
                    ไม่ว่าจะมีสภาพเป็นนิติบุคคลหรือบุคคลธรรมดา ทั้งนี้
                    เพื่อให้การเปิดเผยข้อมูลนั้นอยู่ภายใต้ขอบเขตที่จำเป็น

                </p>

                <p style="font-weight: 500;">สำนักงานฯ ในเครือ</p>
                <p style="text-align:justify;">&nbsp; &nbsp; &nbsp; &nbsp;
                    &nbsp;คู่ค้าทางธุรกิจ ซึ่งอาจมีสถานะเป็นนิติบุคคลหรือบุคคลธรรมดา ที่สำนักงานฯ ได้ทำธุรกรรมด้วย
                    เพื่อดำเนินการให้เป็นไปตามการให้บริการแก่ลูกค้า <br>
                    ผู้ให้บริการ หมายถึง นิติบุคคลหรือบุคคลธรรมดา ที่สำนักงานฯ
                    พิจารณาแล้วเห็นว่าเป็นผู้ที่มีคุณสมบัติที่เหมาะสมในการให้บริการแก่ลูกค้า


                </p>

                <p style="font-weight: 500;">บุคคลอื่นใดตามที่กฎหมายกำหนด</p>
                <p style="text-align:justify;">&nbsp; &nbsp; &nbsp; &nbsp;
                    &nbsp;บุคคลอื่นใด โดยความยินยอมของลูกค้าผู้เป็นเจ้าของข้อมูล
                </p>


                <p style="font-weight: 500;">เจ้าหน้าที่คุ้มครองข้อมูลส่วนบุคคล</p>
                <p style="text-align:justify;">&nbsp; &nbsp; &nbsp; &nbsp;
                    &nbsp;สำนักงานฯ ได้มีการดำเนินการปฏิบัติตามพระราชบัญญัติคุ้มครองข้อมูลส่วนบุคคล พ.ศ. 2562
                    โดยแต่งตั้งเจ้าหน้าที่คุ้มครองข้อมูลส่วนบุคคล (Data Protection Officer : DPO)
                    เพื่อตรวจสอบการดำเนินการของสำนักงานฯ ที่เกี่ยวกับการเก็บรวบรวม
                    ใช้และเปิดเผยข้อมูลส่วนบุคคลให้สอดคล้องกับพระราชบัญญัติคุ้มครองข้อมูลส่วนบุคคล พ.ศ. 2562
                    รวมถึงกฎหมายที่เกี่ยวข้องกับการคุ้มครองข้อมูลส่วนบุคคล นอกจากนี้ สำนักงานฯ ได้จัดทำระเบียบ
                    คำสั่งให้ผู้เกี่ยวข้องดำเนินการตามที่กำหนดไว้
                    เพื่อให้การดำเนินงานตามแนวนโยบายเกี่ยวกับการคุ้มครองข้อมูลส่วนบุคคลให้เป็นไปด้วยความเรียบร้อย
                </p>

                <p style="font-weight: 500;">ช่องทางการติดต่อสำนักงานฯ</p>
                <p style="text-align:justify;">
                    กรณีพบเหตุการละเมิดข้อมูลส่วนบุคคลทั้งของลูกค้าและของพนักงาน สามารถติดต่อได้ที่ <br>
                    E-Mail เจ้าหน้าที่คุ้มครองข้อมูลส่วนบุคคล Data Protection Officer (DPO): dpo@assetwise.co.th <br>
                    ส่งเอกสารหลักฐาน: เจ้าหน้าที่คุ้มครองข้อมูลส่วนบุคคล Data Protection Officer (DPO)
                    ศูนย์ราชการเฉลิมพระเกียรติ ๘๐ พรรษา ๕ ธันวาคม ๒๕๕๐ (อาคาร B) ชั้น9 เลขที่ 120 หมู่ที่ 3 ถนนแจ้งวัฒนะ
                    กรุงเทพมหานคร 10210<br>
                    Contact Center: 02 141 7800 (ทุกวัน เวลา 08.30 – 18.30 น.)

                </p>
            </div>
            <div class="modal-footer">
                <button type="button" id="biogand-condition" class="btn btn-primary" data-dismiss="modal">ตกลง</button>
            </div>
        </div>
    </div>
</div>