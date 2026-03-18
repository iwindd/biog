<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

use backend\models\Province;
use backend\models\District;
use backend\models\Subdistrict;

/* @var $this yii\web\View */
/* @var $model backend\models\School */
/* @var $form yii\widgets\ActiveForm */

$this->registerJsFile(Url::base().'/js/location.js', ['depends' => [\backend\assets\AppAsset::className()]]);
$this->registerJsFile(Url::base().'/js/school.js', ['depends' => [\backend\assets\AppAsset::className()]]);

$province = ArrayHelper::map(Province::find()->all(), 'id', 'name_th');
$district = ArrayHelper::map(District::find()->all(), 'id', 'name_th');
$subdistrict = ArrayHelper::map(Subdistrict::find()->all(), 'id', 'name_th');

?>

<div class="school-form">

    <?php $form = ActiveForm::begin([
        // 'enableClientValidation' => false,
        // 'enableAjaxValidation' => true,
        'validateOnChange' => true,
        'validateOnBlur' => false,
        'options' => [
            'enctype' => 'multipart/form-data',
            'id' => 'dynamic-form'
        ]
        
    ]); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true])->label('ชื่อโรงเรียน *') ?>

    <?= $form->field($model, 'phone')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'address')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'province_id')->dropDownList($province, ['prompt' => 'กรุณาเลือกจังหวัด'])->label('จังหวัด *') ?>

    <?= $form->field($model, 'district_id')->dropDownList($district, ['prompt' => 'กรุณาเลือกอำเภอ'])->label('อำเภอ *') ?>

    <?= $form->field($model, 'subdistrict_id')->dropDownList($subdistrict, ['prompt' => 'กรุณาเลือกตำบล'])->label('ตำบล *') ?>


    <div class="panel panel-default">
        <div class="panel-heading">คุณครูในโรงเรียน</div>
        <div class="panel-body">

            <?= $this->render('_form_teacher', [
                'form' => $form,
                'model' => $model,
                'indexRef' => 1,
                'modelTeacher' => $modelTeacher,
            ]) ?>

        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading">นักเรียนในโรงเรียน</div>
        <div class="panel-body">

            <?= $this->render('_form_student', [
                'form' => $form,
                'model' => $model,
                'indexRef' => 2,
                'modelStudent' => $modelStudent,
            ]) ?>

        </div>
    </div>

    <div class="form-group">
        <?= Html::submitButton('บันทึก', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
