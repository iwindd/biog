<?php
use yii\bootstrap\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \common\models\LoginForm */

$this->title = 'Login';

$fieldOptions1 = [
    'options' => ['class' => 'form-group has-feedback'],
    'inputTemplate' => "{input}<span class='glyphicon glyphicon-envelope form-control-feedback'></span>"
];

$fieldOptions2 = [
    'options' => ['class' => 'form-group has-feedback'],
    'inputTemplate' => "{input}<span class='glyphicon glyphicon-lock form-control-feedback'></span>"
];
?>

<div class="login-box">

    <!-- /.login-logo -->
    <div class="login-box-body">
        <p class="login-box-msg">Login for backend site</p>


        <?php if (Yii::$app->session->hasFlash('alert-login')): ?>
            <?= \yii\bootstrap\Alert::widget([
                'body' => ArrayHelper::getValue(Yii::$app->session->getFlash('alert-login'), 'body'),
                'options' => ArrayHelper::getValue(Yii::$app->session->getFlash('alert-login'), 'options'),
            ]) ?>
        <?php endif; ?>

        <?php $form = ActiveForm::begin(['id' => 'login-form', 'enableClientValidation' => false]); ?>

        <?= $form
            ->field($model, 'login', $fieldOptions1)
            ->label(false)
            ->textInput(['placeholder' => $model->getAttributeLabel('username')]) ?>

        <?= $form
            ->field($model, 'password', $fieldOptions2)
            ->label(false)
            ->passwordInput(['placeholder' => $model->getAttributeLabel('password')]) ?>

        <div class="row">
            <!-- /.col -->
            <div class="col-xs-12">
                <?= Html::submitButton('Login', ['class' => 'btn btn-primary btn-block btn-flat', 'name' => 'login-button']) ?>
            </div>
            <!-- /.col -->
            <div class="col-xs-12">
                <p class=" text-center" style="margin: 10px 0;">หรือ</p>
                <a href="<?= Url::to(['/thaid/auth']) ?>" class="btn btn-primary " style="width: 100%;">
                    เข้าสู่ระบบด้วย ThaID
                </a>
            </div>
        </div>


        <?php ActiveForm::end(); ?>
    </div>
    <!-- /.login-box-body -->
</div><!-- /.login-box -->
