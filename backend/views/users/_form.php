<?php

use backend\models\Community;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use dosamigos\datepicker\DateRangePicker;
use kartik\widgets\FileInput;
use dosamigos\ckeditor\CKEditor;
use kartik\date\DatePicker;
use kartik\widgets\Select2;
use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use kartik\widgets\DepDrop;

use common\components\Upload;

use backend\models\Province;
use backend\models\District;
use backend\models\Subdistrict;

$this->registerJsFile(Url::base().'/js/location.js', ['depends' => [\backend\assets\AppAsset::className()]]);
$this->registerJsFile(Url::base().'/js/user.js', ['depends' => [\backend\assets\AppAsset::className()]]);

$initialPreview = "";  
if(!empty($profile->picture)){
   $initialPreview = '<img src="/files/profile/'.$profile->picture.'" class="img-responsive img-thumbnail " style="width:200px;" alt="" />';
}

/* @var $this yii\web\View */
/* @var $model backend\models\Users */
/* @var $form yii\widgets\ActiveForm */

$status= [
    !empty($model->confirmed_at)?$model->confirmed_at:"1" => 'ยืนยัน', 
    ];

$block= [
    !empty($model->blocked_at)?$model->blocked_at:1 => 'ปิดใช้งาน', 
    ];
$confirmed_at= [
        '1' => "ยืนยัน", 
        '0' => "ไม่ยืนยันตัวตน",              
     
    ];

$gender= [
    'male' => "ชาย", 
    'female' => "หญิง",                      
];

$province = ArrayHelper::map(Province::find()->all(), 'id', 'name_th');
$district = ArrayHelper::map(District::find()->all(), 'id', 'name_th');
$subdistrict = ArrayHelper::map(Subdistrict::find()->all(), 'id', 'name_th');

    
?>

<div class="users-form">

    <?php $form = ActiveForm::begin([
      'options' => ['enctype' => 'multipart/form-data']
    ]); ?>


    <div class="panel panel-default">
        <div class="panel-heading">ข้อมูลผู้ใช้</div>
        <div class="panel-body">
            <?= $form->field($model, 'username')->textInput(['maxlength' => true])->label('ชื่อผู้ใช้ *') ?>

            <?= $form->field($model, 'email')->textInput(['maxlength' => true])->label('อีเมล *') ?>

            <?= $form->field($user, 'new_password')->passwordInput()->label('รหัสผ่าน *') ?>

            <?= $form->field($user, 'confirm_password')->passwordInput()->label('ยืนยันรหัสผ่าน *') ?>


            <?php /*
                $form->field($model, 'confirmed_at')
                     ->dropDownList($confirmed_at, ['prompt' => 'ไม่ยืนยันตัวตน'])
                     ->label('ยืนยันเข้าใช้งาน') */
            ?>

            <?=
                $form->field($model, 'blocked_at')
                     ->dropDownList($block, ['prompt' => 'เปิดใช้งาน'])
                     ->label('สถานะการเข้าระบบ')
            ?>

            <div class="form-group field-users-role">
                <label class="control-label" for="users-role">บทบาท</label><br>
        
                <input type="checkbox" id="role-admin" <?php echo ($admin == 1)? "checked":"" ?> name="roleAdmin" value="1">
                <label for="role-admin"> Admin </label><br>

                <input type="checkbox" id="role-editor" <?php echo ($editor == 1)? "checked":"" ?> name="roleEditor" value="1">
                <label for="role-editor"> Editor </label><br>

                <input type="checkbox" id="role-teacher" <?php echo ($teacher == 1)? "checked":"" ?> name="roleTeacher" value="1">
                <label for="role-teacher"> Teacher </label><br>

                <input type="checkbox" id="role-student" <?php echo ($student == 1)? "checked":"" ?> name="roleStudent" value="1">
                <label for="role-student"> Student </label><br>

                <input type="checkbox" id="role-member" <?php echo ($member == 1)? "checked":"" ?> name="roleMember" value="1">
                <label for="role-member"> Member </label><br>

            </div>
        </div>
    </div>


    <div class="panel panel-default">
        <div class="panel-heading">ข้อมูลส่วนตัวผู้ใช้</div>
        <div class="panel-body">

            <?= $form->field($profile, 'display_name')->textInput(['maxlength' => true])->label('ชื่อแสดงผล *') ?>

            <?= $form->field($profile, 'firstname')->textInput(['maxlength' => true])->label('ชื่อ *') ?>

            <?= $form->field($profile, 'lastname')->textInput(['maxlength' => true])->label('นามสกุล *') ?>

            <?=
                $form->field($profile, 'gender')
                     ->dropDownList($gender, ['prompt' => 'เลือกเพศ'])
                     ->label('เพศ')
            ?>


            <?php /*echo $form->field($profile, 'birthdate')->widget(DatePicker::classname(), [
                'options' => ['placeholder' => 'เลือกวันเกิด ...'],
                'pluginOptions' => [
                    'autoclose'=>true
                ]
            ]);*/ ?>

            <?php
                echo $form->field($profile, 'birthdate')->widget(DatePicker::classname(), [
                    'options' => ['placeholder' => 'วัน/เดือน/ปีเกิด'],
                    'language' => 'th',
                    'pluginOptions' => [
                        'orientation' => 'top right',
                        'format' => 'yyyy-mm-dd',
                        'autoclose' => true
                    ],
                    'type' => DatePicker::TYPE_COMPONENT_APPEND,
                ])->label('วันเกิด');
            ?>      

            <?= $form->field($profile, 'phone')->textInput(['type' => 'tel', 'maxlength' => 10, 'pattern' => '0[689][0-9]{8}'])->label('หมายเลขโทรศัพท์มือถือ *') ?>

            <?= $form->field($profile, 'class')->textInput(['maxlength' => true]) ?>

            <?= $form->field($profile, 'invite_code')->textInput(['maxlength' => true, 'readonly'=> true]) ?>

            <?= $form->field($profile, 'home_number')->textInput(['maxlength' => true]) ?>

            <?= $form->field($profile, 'province_id')->dropDownList($province, ['prompt' => 'กรุณาเลือกจังหวัด']) ?>

            <?= $form->field($profile, 'district_id')->dropDownList($district, ['prompt' => 'กรุณาเลือกอำเภอ']) ?>

            <?= $form->field($profile, 'subdistrict_id')->dropDownList($subdistrict, ['prompt' => 'กรุณาเลือกตำบล']) ?>


            <?php 
                if(!empty($profile->file_key)){
                    ?>
                    <label class="control-label" >รูปภาพผู้ใช้</label>
                    <div class="row" >
                        <div class="col-md-3 " id="main-image-item-<?php echo $profile->user_id; ?>">
                            <?php
                                echo Upload::readfilePicture('profile', $profile->file_key);
                            ?>
                        </div>
                    </div>
                <?php
                }
            ?>
       
            <?= $form->field($profile, 'picture', [
                          'template' => "{label}\n{input}\n<div> ไฟล์รูปภาพมีขนาดไม่เกิน 2 MB และ ต้องเป็นไฟล์นามสกุล jpg, jpeg, png หรือ gif เท่านั้น </div>\n{hint}\n{error}",
                          'labelOptions' => [ 'class' => 'control-label' ]
            ])->widget(FileInput::classname(), [
                //'options' => ['accept' => 'image/*'],
                'pluginOptions' => [
                    'initialPreview'=>$initialPreview,
                    'allowedFileExtensions'=>['jpg', 'jpeg', 'png', 'gif', 'PNG'],
                    'showPreview' => true,
                    'showRemove' => false,
                    'showUpload' => false,
                    'maxFileSize'=>2048
                 ]
            ])->label('รูปภาพ'); ?> 

            <input type="hidden" name="deletePic" id="deletePic" value="0" >




        </div>
    </div>

    <?php /*                   
    <div class="panel panel-default">
        <div class="panel-heading">เลือกชุมชน</div>
        <div class="panel-body">
        <?php 
            echo $form->field($userCommu, 'community_id')->widget(Select2::classname(), [
                'name' => 'kv-type-01',
                'data' => ArrayHelper::map(Community::find()->all(), 'id', 'name_th'),
                'options' => [
                    'placeholder' => 'เลือกชุมชน ...',
                ],
            ])->label("ชุมชน");
            ?>

            <div class="[ form-group ]">
                <input type="checkbox" name="deleteCommu" id="fancy-checkbox-danger" value="1" autocomplete="off" />
                <div class="[ btn-group ]">
                    <label for="fancy-checkbox-danger" class="[ btn btn-danger ]">
                        <span class="[ glyphicon glyphicon-ok ]"></span>
                        <span> </span>
                    </label>
                    <label for="fancy-checkbox-danger" class="[ btn btn-default active ]">
                        ลบสมาชิกออกจากชุมชน
                    </label>
                </div>
            </div>
        </div>
    </div> */ ?>

    <div class="form-group">
        <?= Html::submitButton('บันทึก', ['class' => 'btn btn-success','title'=> 'บันทึก']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>



<?php
 ?>
