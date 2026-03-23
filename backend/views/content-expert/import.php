<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\ContentImportForm */

$this->title = 'นำเข้าข้อมูลภูมิปัญญา/ปราชญ์จาก Excel';
$this->params['breadcrumbs'][] = ['label' => 'จัดการข้อมูลภูมิปัญญา/ปราชญ์', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="content-expert-import">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="well">
        <p>กรุณาเลือกไฟล์ Excel (.xlsx) ที่มีรูปแบบตาม Template ที่กำหนด (แถวที่ 3 เป็นต้นไป)</p>
        
        <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

        <?= $form->field($model, 'importFile')->fileInput() ?>

        <div class="form-group">
            <?= Html::submitButton('Upload และตรวจสอบข้อมูล', ['class' => 'btn btn-primary']) ?>
            <?= Html::a('Download Template', ['/templates/content-expert-import-template.zip'], ['class' => 'btn btn-success', 'download' => true]) ?>
            <?= Html::a('ยกเลิก', ['index'], ['class' => 'btn btn-default']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>

</div>
