<?php

use frontend\models\Banner;
use yii\bootstrap4\ActiveForm;
use yii\bootstrap4\Html;
use yii\helpers\ArrayHelper;

$banner = Banner::find()->where(['slug_url' => 'Login'])->one();

$this->title = 'เข้าสู่ระบบ';
?>

<?php
$backgroundImage = '/images/banner/Login_Banner.png';

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
            <li class="breadcrumb-item"><a href="/login"><?= $this->title ?></a></li>
        </ol>
    </div>
</div>

<div class="container login-container">

    <div class="login-form row d-flex justify-content-center align-items-center">
        <div class="col-lg-5 col-md-7 col-sm-8">

            <?php if (Yii::$app->session->hasFlash('REGISTER_SUCCESS')): ?>
                <?=
                \yii\bootstrap4\Alert::widget([
                    'body' => ArrayHelper::getValue(Yii::$app->session->getFlash('REGISTER_SUCCESS'), 'body'),
                    'options' => ArrayHelper::getValue(Yii::$app->session->getFlash('REGISTER_SUCCESS'), 'options'),
                ])
                ?>
            <?php endif; ?>

            <div class="text-center text-bold mb-4 "><span class="h3">เข้าสู่ระบบ</span></div>

            <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>
            <div class="form-group mb-3">
                <?= $form
                    ->field($model, 'login')
                    ->textInput()
                    ->input('text', [
                        'placeholder' => 'อีเมล'
                    ])
                    ->label('อีเมล'); ?>
            </div>
            <div class="form-group">
                <?= $form
                    ->field($model, 'password')
                    ->textInput()
                    ->input('password', [
                        'placeholder' => 'รหัสผ่าน'
                    ])
                    ->label('รหัสผ่าน'); ?>
            </div>
            <div class="d-flex justify-content-between">
                <div class="form-group">
                    <?php
                    echo $form
                        ->field($model, 'rememberMe', [
                            'options' => [
                                'class' => 'd-inline'
                            ],
                        ])
                        ->checkbox(['checked' => false])
                        ->label('จำบัญชีผู้ใช้ของฉัน', [
                            'class' => 'custom-control-label cursor-pointer'
                        ]);
                    ?>
                </div>
                <div class="forgot-password">
                    <a href="/forget-password" id="forget-password-link">ลืมรหัสผ่าน</a>
                </div>
            </div>
              
            <div class="row mt-4 justify-content-center align-items-center">

                <div class="form-group">
                    <?= $form->field($modelUser, 'reCaptcha')->widget(
                        \himiklab\yii2\recaptcha\ReCaptcha2::className()
                    )->label(false) ?>
                </div>

            </div>  


            <div class="text-center">
                <?= Html::submitButton('เข้าสู่ระบบ', [
                    'class' => 'btn btn-primary btn-block',
                    'name' => 'login-button'
                ]) ?>
            </div>
            
            <div class="text-center mt-3">
                <a class="btn btn-outline-primary btn-block" href="/thaid/auth" style="display: flex; align-items: center; justify-content: center; gap: 10px;">
                    เข้าสู่ระบบด้วย ThaID
                </a>
            </div>
            <?php ActiveForm::end(); ?>

            <div class="text-center text-bold my-4 ">
                <span class="">ยังไม่มีบัญชีสมาชิก?</span>
            </div>

            <div class="text-center">
                <a class="btn btn-outline-secondary btn-block border border-secondary" href="/user/register">สมัครสมาชิกที่นี่</a>
            </div>

        </div>
    </div>

</div>
<?php
$js = <<<JS
$('#login-form').on('beforeValidate', function () {
    $('#forget-password-link').css('pointer-events', 'none').addClass('text-muted');
}).on('afterValidate', function (event, messages, errorAttributes) {
    if (errorAttributes.length > 0) {
        $('#forget-password-link').css('pointer-events', 'auto').removeClass('text-muted');
    }
});
$('#login-form').on('submit', function () {
    $('#forget-password-link').css('pointer-events', 'none').addClass('text-muted');
});
JS;
$this->registerJs($js);
?>