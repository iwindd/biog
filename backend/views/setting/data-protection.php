<?php

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

$this->title = 'เงื่อนไขและนโยบายคุ้มครองข้อมูลส่วนบุคคล';
$this->params['breadcrumbs'][] = $this->title;

$urlFrontend = isset(Yii::$app->params['urlFrontend']) ? Yii::$app->params['urlFrontend'] : '';
?>
<div class="variables-index">

<h1><?= Html::encode($this->title) ?></h1>
<?php if (Yii::$app->session->hasFlash('alert')): ?>
        <?= \yii\bootstrap\Alert::widget([
            'body' => ArrayHelper::getValue(Yii::$app->session->getFlash('alert'), 'body'),
            'options' => ArrayHelper::getValue(Yii::$app->session->getFlash('alert'), 'options'),
        ]) ?>
    <?php endif; ?>
    
<?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>
    <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">

    <div class="panel setting-page panel-default">
        <div class="panel-heading"><h4><i class="fa fa-edit"></i> เนื้อหา</h4></div>
        <div class="panel-body">
            <div class="form-group required">
                <?= $form->field($model, 'data_protection')->textarea(['rows' => '6', 'class' => 'summernote-data_protection'])->label('เงื่อนไขและนโยบายคุ้มครองข้อมูลส่วนบุคคล') ?>
            </div>
        </div>
    </div>

    <!-- PDF Upload Section -->
    <div class="panel panel-default">
        <div class="panel-heading"><h4><i class="fa fa-file-pdf-o"></i> ไฟล์ PDF คุ้มครองข้อมูลส่วนบุคคล</h4></div>
        <div class="panel-body">
            <?php if (!empty($currentPdf)): ?>
                <div class="current-pdf-section" style="margin-bottom: 20px;">
                    <label><strong><i class="fa fa-check-circle text-success"></i> ไฟล์ปัจจุบัน:</strong></label>
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                        <a href="<?= $urlFrontend . $currentPdf ?>" target="_blank" class="btn btn-sm btn-info">
                            <i class="fa fa-external-link"></i> เปิดไฟล์ PDF
                        </a>
                        <?= Html::a(
                            '<i class="fa fa-trash"></i> ลบไฟล์ PDF',
                            ['/setting/delete-protection-pdf'],
                            [
                                'class' => 'btn btn-sm btn-danger',
                                'data' => [
                                    'confirm' => 'คุณต้องการลบไฟล์ PDF นี้หรือไม่?',
                                    'method' => 'post',
                                ],
                            ]
                        ) ?>
                    </div>
                    <div class="pdf-preview-current" style="border: 1px solid #ddd; border-radius: 4px; overflow: hidden;">
                        <iframe src="<?= $urlFrontend . $currentPdf ?>" width="100%" height="500px" style="border: none;" title="ตัวอย่าง PDF ปัจจุบัน"></iframe>
                    </div>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label for="pdf-upload"><strong>อัพโหลดไฟล์ PDF ใหม่:</strong></label>
                <input type="file" id="pdf-upload" name="Variables[data_protection_pdf]" accept=".pdf" class="form-control" style="padding: 6px;">
                <p class="help-block">รองรับเฉพาะไฟล์ PDF ขนาดไม่เกิน 10 MB</p>
            </div>

            <!-- Client-side Preview -->
            <div id="pdf-preview-new" style="display: none; margin-top: 15px;">
                <label><strong><i class="fa fa-eye"></i> ตัวอย่างไฟล์ที่เลือก:</strong></label>
                <div style="border: 1px solid #ddd; border-radius: 4px; overflow: hidden;">
                    <iframe id="pdf-preview-frame" width="100%" height="500px" style="border: none;" title="ตัวอย่างไฟล์ PDF ที่เลือก"></iframe>
                </div>
            </div>
        </div>
    </div>

    <div class="form-group">
        <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> บันทึก</button>
    </div>
    
    <?php ActiveForm::end(); ?>
</div>

<?php
$js = <<<JS
document.getElementById('pdf-upload').addEventListener('change', function(e) {
    var file = e.target.files[0];
    var previewSection = document.getElementById('pdf-preview-new');
    var previewFrame = document.getElementById('pdf-preview-frame');
    
    if (file && file.type === 'application/pdf') {
        var fileURL = URL.createObjectURL(file);
        previewFrame.src = fileURL;
        previewSection.style.display = 'block';
    } else {
        previewSection.style.display = 'none';
        previewFrame.src = '';
        if (file) {
            alert('กรุณาเลือกไฟล์ PDF เท่านั้น');
            e.target.value = '';
        }
    }
});
JS;
$this->registerJs($js);
?>
<?php
Yii::$app->getSession()->remove('success');
?>
