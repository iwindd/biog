<?php


use yii\bootstrap\Html;
use common\components\_;
use yii\bootstrap\ActiveForm;



$this->title = 'ตั้งค่า facebook auto post';
// $this->params['breadcrumbs'][] = ['label' => 'Settings', 'url' => ['index']];
// $this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
// $this->params['breadcrumbs'][] = 'Update';
?>

<div class="settings-facebook-auto-post">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="settings-form">

        <?php $form = ActiveForm::begin(); ?>

        <?php
        if (($settingValue->facebook_application_id && $settingValue->facebook_application_secrete) && !$settingValue->facebook_access_token) :
        ?>
            <div class="form-group">
                <?= Html::button('ขอสิทธิ์', ['class' => 'btn btn-primary']) ?>
            </div>
        <?php
        elseif (($settingValue->facebook_application_id && $settingValue->facebook_application_secrete) && $settingValue->facebook_access_token) :
        ?>
            <div class="form-group">
                <?= Html::button('ขอสิทธิ์อีกครั้ง', ['class' => 'btn btn-primary']) ?>
            </div>
        <?php
        endif;
        ?>

        <?= $form->field($settingValue, "facebook_auto_post")->checkbox(['checked' => false]); ?>

        <?= $form->field($settingValue, 'facebook_application_id')->textInput() ?>

        <?= $form->field($settingValue, 'facebook_application_secrete')->textInput() ?>

        <?= $form->field($settingValue, 'facebook_access_token')->textInput(['disabled' => true]) ?>

        <div class="form-group">
            <?= Html::submitButton('บันทึก', ['class' => 'btn btn-success']) ?>
        </div>

        <?php ActiveForm::end(); ?>

    </div>

</div>