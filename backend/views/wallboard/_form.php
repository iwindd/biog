<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model backend\models\Wallboard */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="wallboard-form">

    <?php $form = ActiveForm::begin([
        'options' => ['enctype' => 'multipart/form-data'],
    ]); ?>

    <?php /*    
    <?php echo $form->field($model, 'description')->widget(\yii2jodit\JoditWidget::className(), [
                    'settings' => [
                        'height' => '300px',
                        'enableDragAndDropFileToEditor' => new \yii\web\JsExpression("true"),
                        'buttons' => [
                            'source', '|',
                            'bold', 'strikethrough', 'underline', 'italic', 'align', '|',
                            'ul', 'ol', 'outdent', 'indent', 'font', 'fontsize', 'brush', 'paragraph', 'eraser', '|',
                            'image', 'video', 'file', 'table', 'link', '|',
                            'align', 'undo', 'redo',
                        ],
                    ],
                    'options' => ['placeholder' => 'Wallboard'],

    ]); ?> */ ?>

    <?= $form->field($model, 'description')->textarea(['rows' => '6', 'class' => 'summernote-description'])->label('รายละเอียด *') ?>

    <div class="form-group">
        <?= Html::submitButton('บันทึก', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
