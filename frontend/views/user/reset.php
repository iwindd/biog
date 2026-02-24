<?php

use yii\bootstrap4\Html;
use yii\helpers\ArrayHelper;
use yii\bootstrap4\ActiveForm;

$this->title = Yii::t('app', 'แก้ไขรหัสผ่านใหม่');

use frontend\models\Banner;
$banner = Banner::find()->where(['slug_url' => 'Reset Password'])->one();


$backgroundImage =  '/images/banner/Register_Banner.png';

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
            <li class="breadcrumb-item"><a href="#"><?= $this->title ?></a></li>
        </ol>
    </div>
</div>

<div class="container reset-password-container">

    <div class="reset-password-form row d-flex justify-content-center">
        <div class="col-md-4 col-sm-12">
                <?php $form = ActiveForm::begin([
                    'id' => 'password-recovery-form',
                    'enableAjaxValidation' => true,
                    //'enableClientValidation' => false,
                ]); ?>

                <?php /*
                <?= $form->field($model, 'password',[
                  'template' => '<label> '.\Yii::t('app','รหัสผ่านใหม่').' </label><div class="input-group ">{input}
                  <span class="input-group-addon showPasswordReset"><i class="fa fa-eye " aria-hidden="true"></i></span></div>{error}{hint}'
                  ])->passwordInput()->label(\Yii::t('app','รหัสผ่านใหม่')) ?>
                  

                <?= $form->field($model, 'confirm_password',[
                  'template' => '<label>'.\Yii::t('app','ยืนยันรหัสผ่าน').' </label><div class="input-group ">{input}
                  <span class="input-group-addon showPasswordConfirmReset"><i class="fa fa-eye" aria-hidden="true"></i></span></div>{error}{hint}'
                  ])->passwordInput()->label(\Yii::t('app','ยืนยันรหัสผ่าน')) ?> */ ?>


                <?= $form->field($model, 'password')->passwordInput()->label(\Yii::t('app','รหัสผ่านใหม่')) ?>

                <?= $form->field($model, 'confirm_password')->passwordInput()->label(\Yii::t('app','ยืนยันรหัสผ่านใหม่')) ?>

                <?= Html::submitButton(Yii::t('user', 'ตกลง'), ['class' => 'btn btn-primary btn-block mt-5']) ?><br>

                <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>