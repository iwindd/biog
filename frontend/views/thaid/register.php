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

$this->title = Yii::t('app', 'เปิดบัญชีและลงทะเบียนด้วย ThaID');

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
            <li class="breadcrumb-item home"><a href="/">Home</a></li>
            <li class="breadcrumb-item"><a href="/login">อัปเดตข้อมูลของ ThaID</a></li>
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
                <div class="col-md-12 d-flex justify-content-between mb-4">
                    <div class="">
                        <span class="h6">ข้อมูลสมาชิกจาก ThaID (กรุณาเติมข้อมูลให้ครบถ้วน)</span>
                        <p class="text-muted small">เพื่อให้การใช้งานในระบบสมบูรณ์ กรุณากรอกอีเมลและข้อมูลส่วนตัว</p>
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
                        ])
                        ->label('อีเมล *'); ?>

                </div>

            </div>

             <div style="display:none;">
                <?= clone $form
                    ->field($userModel, 'new_password')
                    ->hiddenInput(['value' => 'ThaID@Temp123'])
                    ->label(false) ?>
                <?= clone $form
                    ->field($userModel, 'confirm_password')
                    ->hiddenInput(['value' => 'ThaID@Temp123'])
                    ->label(false) ?>
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
                    <?= $form->field($profileModel, 'phone')->textInput()->input('tel', ['placeholder' => 'หมายเลขโทรศัพท์มือถือ', 'maxlength' => 10, 'pattern' => '0[689][0-9]{8}'])->label('หมายเลขโทรศัพท์มือถือ *'); ?>

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

                <?= $form->field($userModel, 'accept_biog')->checkbox(['uncheck' => false])->label('ยอมรับ <a href="/terms-conditions" target="_blank">เงื่อนไขและนโยบายของ BIOGANG.NET</a>'); ?>


                <?= $form->field($userModel, 'accept_condition', ['options' => ['class' => 'd-inline']])->checkbox(['uncheck' => false])->label('ยอมรับ <a href="/data-protection-policy" target="_blank">เงื่อนไขและนโยบายคุ้มครองข้อมูลส่วนบุลคล</a>'); ?>

            </div>

        </div>




        <div class="row mt-4 justify-content-center align-items-center">

            <div class="form-group">
                <?= Html::submitButton('ลงทะเบียนด้วย ThaID', ['class' => 'btn btn-primary btn-register', 'name' => 'register-button']) ?>
            </div>
        </div>

        <?php ActiveForm::end(); ?>


    </div>
</div>

</div>
