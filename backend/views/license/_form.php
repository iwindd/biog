<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\License */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="license-form">

    <?php $form = ActiveForm::begin(); ?>

    <div class="box box-primary">
        <div class="box-body">
            <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'code')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>

            <?= $form->field($model, 'url')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="box-footer">
            <div class="form-group text-right">
                <?= Html::submitButton('บันทึก', ['class' => 'btn btn-success']) ?>
                <?= Html::a('ยกเลิก', ['index'], ['class' => 'btn btn-default']) ?>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
