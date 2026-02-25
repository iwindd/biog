<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\ShortUrl */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="short-url-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'target_url')->textInput(['maxlength' => true, 'placeholder' => 'https://example.com/very/long/url...']) ?>

    <?= $form->field($model, 'code')->textInput(['maxlength' => true, 'placeholder' => 'เว้นว่างไว้หากต้องการให้ระบบสุ่มรหัสให้อัตโนมัติ']) ?>

    <div class="form-group">
        <?= Html::submitButton('บันทึก', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
