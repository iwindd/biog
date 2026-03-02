<?php

use yii\helpers\Html;
use yii\helpers\Url;
use backend\components\ImportHelper;

/* @var $this yii\web\View */
/* @var $data array */

$this->title = 'ตรวจสอบข้อมูลที่นำเข้า';
$this->params['breadcrumbs'][] = ['label' => 'จัดการข้อมูลผลิตภัณฑ์', 'url' => ['index']];
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
            <?php $errors = ImportHelper::validateProductImportItem($item); ?>
            <div class="col-md-12 import-item-card" style="margin-bottom: 15px;">
                <div class="panel <?= empty($errors) ? 'panel-success' : 'panel-warning' ?>">
                    <div class="panel-heading">
                        <div class="row">
                            <div class="col-sm-8">
                                <h3 class="panel-title">
                                    <strong>#<?= $index + 1 ?></strong> — <?= Html::encode($item['name'] ?? 'ไม่มีชื่อ') ?>
                                    <?php if (!empty($errors)): ?>
                                        <span class="label label-warning"><i class="glyphicon glyphicon-warning-sign"></i> <?= count($errors) ?> ข้อผิดพลาด</span>
                                    <?php else: ?>
                                        <span class="label label-success"><i class="glyphicon glyphicon-ok"></i> ผ่าน</span>
                                    <?php endif; ?>
                                </h3>
                            </div>
                            <div class="col-sm-4 text-right">
                                <small class="text-muted">
                                    สถานะ: <strong><?= Html::encode($item['status'] ?? 'pending') ?></strong>
                                    | การแสดงผล: <strong><?= ($item['is_hidden'] ?? 0) ? 'ซ่อน' : 'แสดงผล' ?></strong>
                                </small>
                            </div>
                        </div>
                    </div>
                    <div class="panel-body">
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger" style="margin-bottom: 10px; padding: 8px 12px;">
                                <strong><i class="glyphicon glyphicon-exclamation-sign"></i> ข้อผิดพลาด:</strong>
                                <ul style="margin-bottom: 0; padding-left: 20px;">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?= Html::encode($error) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <div class="full-view-content">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>หมวดหมู่:</strong> <?= Html::encode($item['product_category_name'] ?? '-') ?></p>
                                    <p><strong>จุดเด่น/ประโยชน์:</strong> <?= Html::encode(mb_substr($item['product_features'] ?? '-', 0, 200)) ?><?= mb_strlen($item['product_features'] ?? '') > 200 ? '...' : '' ?></p>
                                    <p><strong>ราคาขาย:</strong> <?= Html::encode($item['product_price'] ?? '-') ?></p>
                                    <p><strong>สถานที่ผลิต/จำหน่าย:</strong> <?= Html::encode($item['product_distribution_location'] ?? '-') ?></p>
                                    <p><strong>ที่อยู่:</strong> <?= Html::encode($item['product_address'] ?? '-') ?></p>
                                    <p><strong>เบอร์โทรศัพท์:</strong> <?= Html::encode($item['product_phone'] ?? '-') ?></p>
                                    <p><strong>ภูมิภาค/จังหวัด:</strong> <?= Html::encode(($item['region'] ?? '-') . ' / ' . ($item['province'] ?? '-')) ?></p>
                                    <p><strong>อำเภอ/ตำบล:</strong> <?= Html::encode(($item['district'] ?? '-') . ' / ' . ($item['subdistrict'] ?? '-')) ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>วัตถุดิบหลัก:</strong> <?= Html::encode($item['product_main_material'] ?? '-') ?></p>
                                    <p><strong>แหล่งวัตถุดิบ:</strong> <?= Html::encode($item['product_sources_material'] ?? '-') ?></p>
                                    <p><strong>แหล่งที่พบ:</strong> <?= Html::encode($item['found_source'] ?? '-') ?></p>
                                    <p><strong>ข้อมูลการติดต่อ:</strong> <?= Html::encode(mb_substr($item['contact'] ?? '-', 0, 200)) ?><?= mb_strlen($item['contact'] ?? '') > 200 ? '...' : '' ?></p>
                                    <?php if (!empty($item['license_name'])): ?>
                                        <p><strong>สัญญาอนุญาต:</strong> <?= Html::encode($item['license_name']) ?></p>
                                        <p><small class="text-muted"><?= Html::encode($item['license_description'] ?? '') ?></small></p>
                                    <?php endif; ?>
                                    <p><strong>Tags:</strong> <?= Html::encode($item['taxonomy_names'] ?? '-') ?></p>
                                    <p><strong>หมายเหตุ:</strong> <?= Html::encode($item['note'] ?? '-') ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
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
