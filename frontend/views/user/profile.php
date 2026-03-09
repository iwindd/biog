<?php

use yii\bootstrap4\Html;
use kartik\date\DatePicker;
use yii\helpers\ArrayHelper;
use yii\bootstrap4\ActiveForm;
use yii\helpers\Url;
use kartik\select2\Select2;

use frontend\models\Region;
use frontend\models\District;
use frontend\models\Subdistrict;
use frontend\models\Zipcode;
use frontend\models\School;
use frontend\models\Province;
use frontend\models\UserSchool;
use frontend\components\PermissionAccess;

$region = ArrayHelper::map(Region::find()->all(), 'id', 'name_th');
$province = ArrayHelper::map(Province::find()->all(), 'id', 'name_th');

$district = array();
if(!empty($profileModel->province_id)){
    $district = ArrayHelper::map(District::find()->where(['province_id' => $profileModel->province_id])->all(), 'id', 'name_th');
}

$subdistrict = array();
if(!empty($profileModel->district_id)){
    $subdistrict = ArrayHelper::map(Subdistrict::find()->where(['district_id' => $profileModel->district_id])->all(), 'id', 'name_th');
}


$zipcode = array();
$dataSchool = ArrayHelper::map(School::find()->all(), 'id', 'name');


$this->title = Yii::t('app', 'ข้อมูลส่วนตัว');


$this->registerJsFile(Url::base().'/js/location.js', ['depends' => [\frontend\assets\AppAsset::className()]]);
$this->registerJsFile(Url::base().'/js/image-preview.js', ['depends' => [\frontend\assets\AppAsset::className()]]);
$this->registerJsFile(Url::base().'/js/user-profile.js', ['depends' => [\frontend\assets\AppAsset::className()]]);

$tempplate = [
    'template' => '{label} <div class="form-group-login"> {input}<div class="input-group-addon copyInvite" title="Copy"><i class="fa fa-copy" aria-hidden="true"></i></div> </div> {error}'
];

use frontend\models\Banner;
$banner = Banner::find()->where(['slug_url' => 'Profile'])->one();


$backgroundImage = '/images/banner/Login_Banner.png';

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
            <li class="breadcrumb-item"><a href="/profile"><?= $this->title ?></a></li>
        </ol>
    </div>
</div>

<div class="container profile-container">

    <?php if (Yii::$app->session->hasFlash('edit_success')) : ?>
        <?=
            \yii\bootstrap4\Alert::widget([
                'body' => ArrayHelper::getValue(Yii::$app->session->getFlash('edit_success'), 'body'),
                'options' => ArrayHelper::getValue(Yii::$app->session->getFlash('edit_success'), 'options'),
            ])
        ?>
    <?php endif; ?>

    
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

    <div class="profile-form row ">
        
        <?php $form = ActiveForm::begin(['id' => 'user-login-form']); ?>
        <div class="col">
            <div class="row justify-content-center">


                <div class="col-lg-6 col-md-12 col-border -left">

                    <div class="mb-4 d-flex justify-content-between align-items-center">
                        <span class="h6">ข้อมูลส่วนตัว</span>
                        <div>
                            <?php if ($userModel->userThaid): ?>
                                <span class="badge badge-success px-3 py-2 mr-2" style="font-size: 0.9rem;">
                                    <i class="fas fa-check-circle mr-1"></i> เชื่อมต่อกับ ThaID แล้ว
                                </span>
                                <a href="<?= Url::to(['/thaid/disconnect']) ?>" 
                                   class="btn btn-outline-danger btn-sm"
                                   onclick="return confirm('คุณต้องการยกเลิกการเชื่อมต่อกับ ThaID ใช่หรือไม่?')">
                                    ยกเลิกการเชื่อมต่อ
                                </a>
                            <?php else: ?>
                                <a href="<?= Url::to(['/thaid/auth']) ?>" class="btn btn-primary btn-sm">
                                    เชื่อมต่อบัญชีด้วย ThaID
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php 
                        if(!empty($userRole->role_id)){
                            if($userRole->role_id == 6){
                                ?>  
                                    <div class="alert alert-warning mb-3" role="alert">
                                        สถานะอาจารย์: กำลังรอตรวจสอบโดยแอดมิน
                                    </div>
                                    
                                <?php
                            }elseif($userRole->role_id == 7){
                                ?>  
                                    <div class="alert alert-warning mb-3" role="alert">
                                        สถานะนักเรียน: กำลังรอตรวจสอบโดยแอดมิน
                                    </div>
                                    
                                <?php
                            }
                        }
                    ?>

                    

                    <?= $form->field($profileModel, 'display_name')->textInput()->input('text', ['placeholder' => "ชื่อที่ใช้แสดง"])->label('ชื่อที่ใช้แสดง');  ?>
                    <?= $form->field($profileModel, 'firstname')->textInput()->input('text', ['placeholder' => "ชื่อ"])->label('ชื่อ');  ?>
                    <?= $form->field($profileModel, 'lastname')->textInput()->input('text', ['placeholder' => "นามสกุล"])->label('นามสกุล');  ?>
                    <?= $form->field($profileModel, 'phone')->textInput()->input('text', ['placeholder' => "หมายเลขโทรศัพท์มือถือ"])->label('หมายเลขโทรศัพท์มือถือ');  ?>

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

                    <?= $form->field($profileModel, 'invite_friend', $tempplate)->textInput()->input('text')->label('เชิญเพื่อนสมัครสมาชิก');  ?>

                    <div class="mt-4 mb-2">
                        <span class="h6">ที่อยู่</span>
                    </div>

                    <?= $form->field($profileModel, 'home_number')->textInput()->input('text', ['placeholder' => "บ้านเลขที่/หมู่ที่-หมู่บ้าน/ซอย/ถนน"])->label('บ้านเลขที่/หมู่ที่-หมู่บ้าน/ซอย/ถนน');  ?>
                    
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
                            <?= $form->field($profileModel, 'zipcode_id')->textInput()->input('text', ['placeholder' => "รหัสไปรษณีย์"])->label('รหัสไปรษณีย์');  ?>
                        </div>
                    </div>

                </div>

                <div class="col-lg-6 col-md-12 col-border -right">

                    <div class="row justify-content-center mb-4">
                        <div>
                            <div>
                                <?php if(!empty($profileModel->picture)){ ?>
                                    <img src="/files/profile/<?php echo $profileModel->picture; ?>" class="profile-image border border-secondary rounded-circle img-fluid">
                                <?php }else{ ?>
                                    <img src="/images/default-user.png" class="profile-image border border-secondary rounded-circle img-fluid">
                                <?php } ?>
                            </div>
                            <div class="d-flex justify-content-end">
                                <div class="wrap-btn-upload">
                                    <?= $form->field($profileModel, 'picture')->fileInput(['style' => "display: none;"])->label(false) ?>
                                    <div class="btn-upload-profile btn btn-secondary d-flex justify-content-center align-items-center">
                                        <i class="align-self-center fas fa-camera"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
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
                    <?php if(empty($userModel->role)){ ?>
                        <?=
                            $form->field($userModel, 'role', ['errorOptions' => ['tag' => 'small']])->inline()->radioList([
                                'student' => 'นักเรียน/นิสิต',
                                'teacher' => 'อาจารย์',
                            ])->label(false);
                        ?>
                    <?php }else if($userModel->role == 'student'){ ?>
                        <?=
                        $form->field($userModel, 'role', ['errorOptions' => ['tag' => 'small']])->inline()->radioList([
                            'student' => 'นักเรียน/นิสิต',
                        ])->label(false);
                        ?>

                        <?= $form->field($profileModel, 'class')->textInput()->input('text', ['placeholder' => "ชั้นเรียน"])->label('ชั้นเรียน');  ?>

                    <?php }else if($userModel->role == 'teacher'){ ?>
                        <?=
                            $form->field($userModel, 'role', ['errorOptions' => ['tag' => 'small']])->inline()->radioList([
                                'teacher' => 'อาจารย์',
                            ])->label(false);
                        ?>

                        <?= $form->field($profileModel, 'major')->textInput()->input('text', ['placeholder' => "ประจำสาขา/วิชา"])->label('ประจำสาขา/วิชา');  ?>

                    <?php } ?>
                    <?php
                        echo $form->field($studentTeacherModel, 'teacher')->widget(Select2::classname(), [
                            'data' => $dataTeacherSchool,
                            'options' => ['placeholder' => 'เลือกคุณครูที่ปรึกษา'],
                            'pluginOptions' => [
                                'allowClear' => true,
                                'multiple' => true
                            ],
                        ])->label('เลือกคุณครูที่ปรึกษา');

                    ?>
                </div>

            </div>

            <?php if(PermissionAccess::FrontendAccess('login_frontend', 'function')): ?>
                <div class="row justify-content-center mt-5">
                    <div class="col align-self-center text-center">
                        <?= Html::submitButton('บันทึก', [
                            'class' => 'btn btn-primary btn-edit mt-3',
                            'name' => 'register-button'
                        ]) ?>
                    </div>
                </div>

            <?php endif; ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>

</div>