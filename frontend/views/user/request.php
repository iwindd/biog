<?php

use yii\bootstrap4\Html;
use yii\helpers\ArrayHelper;
use yii\bootstrap4\ActiveForm;

$this->title = Yii::t('app', 'ลืมรหัสผ่าน');

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

            <?php if (Yii::$app->session->hasFlash('alert-request')) : ?>
                <?=
                    \yii\bootstrap4\Alert::widget([
                        'body' => ArrayHelper::getValue(Yii::$app->session->getFlash('alert-request'), 'body'),
                        'options' => ArrayHelper::getValue(Yii::$app->session->getFlash('alert-request'), 'options'),
                    ])
                ?>
            <?php endif; ?>

            <div class="text-center mb-4">
                <span class="h5 d-block">ลืมรหัสผ่าน ?</span>
                <span class="">กรุณากรอกอีเมลในช่องด้านล่างเพื่อการตั้งค่ารหัสใหม่</span>
            </div>

            <?php $form = ActiveForm::begin(['id' => 'user-login-form']); ?>

            <div class="row">

                <div class="form-group col-lg-12">
                    <?= $form->field($userModel, 'email')
                        ->textInput()->input('text', [
                            'placeholder' => "อีเมล"
                        ])
                        ->label('อีเมล');  ?>
                </div>

            </div>


            <div class="text-center">
                <?= Html::submitButton('ส่ง', [
                    'class' => 'btn btn-primary btn-block',
                    'name' => 'register-button'
                ]) ?>
            </div>

            <?php ActiveForm::end(); ?>


        </div>
    </div>

</div>