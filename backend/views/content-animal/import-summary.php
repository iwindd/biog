<?php

use yii\helpers\Html;
use yii\helpers\Url;
use backend\components\ImportHelper;

/* @var $this yii\web\View */
/* @var $data array */

$this->title = 'ตรวจสอบข้อมูลที่นำเข้า';
$this->params['breadcrumbs'][] = ['label' => 'จัดการข้อมูลสัตว์', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => 'นำเข้าข้อมูล', 'url' => ['import']];
$this->params['breadcrumbs'][] = $this->title;

$urlFrontend = isset(Yii::$app->params['urlFrontend']) ? Yii::$app->params['urlFrontend'] : '';
?>
<div class="import-summary">

    <div class="row" style="margin-bottom: 20px;">
        <div class="col-sm-8">
            <h1 style="margin-top: 0;"><?= Html::encode($this->title) ?></h1>
        </div>
        <div class="col-sm-4 text-right">
            <div class="btn-group" data-toggle="buttons">
                <label class="btn btn-primary active btn-view-mode" data-mode="full">
                    <input type="radio" name="viewMode" autocomplete="off" checked> <i class="glyphicon glyphicon-th-list"></i> แสดงแบบเต็ม
                </label>
                <label class="btn btn-primary btn-view-mode" data-mode="summary">
                    <input type="radio" name="viewMode" autocomplete="off"> <i class="glyphicon glyphicon-th"></i> แสดงแบบย่อ
                </label>
            </div>
        </div>
    </div>

    <div class="alert alert-info">
        พบข้อมูลทั้งหมด <?= count($data) ?> รายการ กรุณาตรวจสอบความถูกต้องก่อนกดบันทึก
    </div>

    <div class="row import-card-container">
        <?php foreach ($data as $index => $item): ?>
            <?= $this->render('//shared/_import-summary-card', [
                'item' => $item,
                'index' => $index,
                'errors' => ImportHelper::validateImportItem($item),
                'urlFrontend' => $urlFrontend,
            ]) ?>
        <?php endforeach; ?>
    </div>

    <hr>

    <div class="form-group">
        <?php if (count($data) > 0): ?>
            <?= Html::a('<i class="glyphicon glyphicon-ok"></i> ยืนยันบันทึกเป็นฉบับร่าง', ['import-confirm'], [
                'class' => 'btn btn-success btn-lg',
                'data' => [
                    'confirm' => 'คุณต้องการบันทึกข้อมูลทั้งหมด ' . count($data) . ' รายการเป็นฉบับร่างใช่หรือไม่?',
                    'method' => 'post',
                ],
            ]) ?>
        <?php endif; ?>
        <?= Html::a('ยกเลิกและทำใหม่', ['import'], ['class' => 'btn btn-default btn-lg']) ?>
    </div>

</div>

<?php
$js = <<<JS
$('.btn-view-mode').click(function() {
    var mode = $(this).data('mode');
    if (mode === 'summary') {
        $('.full-view-content').slideUp();
        $('.import-item-card').removeClass('col-md-12').addClass('col-md-6 col-lg-4');
    } else {
        $('.full-view-content').slideDown();
        $('.import-item-card').removeClass('col-md-6 col-lg-4').addClass('col-md-12');
    }
});
JS;
$this->registerJs($js);
?>
<style>
.import-summary .panel-heading {
    padding: 10px 15px;
}
.import-summary p {
    margin-bottom: 5px;
}
.import-summary .panel-title {
    font-size: 14px;
}
</style>
