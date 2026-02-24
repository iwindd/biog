<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

use kartik\widgets\FileInput;

use common\components\Upload;

/* @var $this yii\web\View */
/* @var $model backend\models\Banner */
/* @var $form yii\widgets\ActiveForm */

$initialPreview = "";  
if(!empty($model->picture_path)){
   $initialPreview = '<img src="'.Yii::$app->params['urlWebBiog'].'/files/banner/'.$model->picture_path.'" class="img-responsive img-thumbnail " style="width:200px;" alt="" />';
}

?>

<div class="banner-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'slug_url')->textInput(['maxlength' => true, 'readonly' => true]) ?>

    <?= $form->field($model, 'picture_path', [
                          'template' => "{label}\n{input}\n<div> รูปภาพควรมีขนาด 1903x502 pixel มีขนาดไม่เกิน 2 MB และ ต้องเป็นไฟล์นามสกุล jpg, jpeg, png หรือ gif เท่านั้น </div>\n{hint}\n{error}",
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
            ])->label('รูปภาพแบนเนอร์'); ?> 


    <div class="form-group">
        <?= Html::submitButton('บันทึก', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
