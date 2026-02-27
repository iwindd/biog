<?php

use yii\helpers\Html;
use yii\helpers\Url;
use common\components\Formatter;

/* @var $this yii\web\View */
/* @var $data array */

$this->title = 'ตรวจสอบข้อมูลที่นำเข้า';
$this->params['breadcrumbs'][] = ['label' => 'จัดการข้อมูลพืช', 'url' => ['index']];
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
            <?php
                $errors = [];
                // Validation matching Content & ContentPlant rules
                if (empty($item['name'])) $errors[] = "ไม่มีชื่อเรื่อง (Required)";
                if (empty($item['characteristics'])) $errors[] = "ไม่มีลักษณะ/คุณสมบัติ (Required)";
                if (empty($item['scientific_name'])) $errors[] = "ไม่มีชื่อวิทยาศาสตร์ (Required)";
                if (empty($item['picture_path'])) $errors[] = "ไม่มีรูปภาพปก (Required)";
                if (empty($item['license_id'])) $errors[] = "ไม่พบสัญญาอนุญาต (Required)";
                
                // Geographical validation
                if (empty($item['province_id']) || empty($item['district_id']) || empty($item['subdistrict_id'])) {
                    $errors[] = "ข้อมูลที่ตั้งไม่ครบถ้วน (รหัสจังหวัด/อำเภอ/ตำบล)";
                }
                if (empty($item['latitude']) || empty($item['longitude'])) {
                    $errors[] = "พิกัดไม่ถูกต้อง";
                }

                if (!empty($item['picture_error'])) $errors[] = $item['picture_error'];
                if (!empty($item['license_error'])) $errors[] = $item['license_error'];

                foreach (['illustration_errors', 'image_sources_errors', 'data_sources_errors'] as $errKey) {
                    if (!empty($item[$errKey])) {
                        foreach ($item[$errKey] as $err) $errors[] = $err;
                    }
                }
                
                $hasError = !empty($errors);
            ?>
            <div class="col-md-12 import-item-card" data-error="<?= $hasError ? '1' : '0' ?>">
                <div class="panel <?= $hasError ? 'panel-danger' : 'panel-default' ?>" style="box-shadow: 0 2px 5px rgba(0,0,0,0.1); border-radius: 8px; overflow: hidden; margin-bottom: 20px;">
                    <div class="panel-heading" style="display: flex; align-items: center; justify-content: space-between;">
                        <h4 class="panel-title" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 80%;">
                            <strong>#<?= $index + 1 ?>:</strong> <?= Html::encode($item['name']) ?>
                        </h4>
                        <span class="label <?= $hasError ? 'label-danger' : 'label-success' ?>">
                            <?= $hasError ? 'มีข้อผิดพลาด' : 'ปกติ' ?>
                        </span>
                    </div>
                    <div class="panel-body">
                        <?php
                        $displayEmpty = function($value) {
                            return (!empty($value) || $value === 0 || $value === '0') ? Html::encode($value) : '<span class="text-muted">(ไม่ระบุ)</span>';
                        };
                        ?>
                        <div class="row">
                            <div class="col-xs-4">
                                <strong>รูปภาพหน้าปก:</strong>
                                <?php if (!empty($item['picture_path'])): ?>
                                    <?= Html::img($urlFrontend . $item['picture_path'], ['class' => 'img-responsive img-thumbnail', 'style' => 'width: 100%; height: 100px; object-fit: cover;']) ?>
                                    <small class="text-muted"><?= Html::encode($item['picture_path_label']) ?></small>
                                <?php else: ?>
                                    <div class="img-thumbnail text-center" style="width: 100%; height: 100px; line-height: 100px; background: #eee;">(ไม่มีรูป)</div>
                                <?php endif; ?>
                            </div>
                            <div class="col-xs-8">
                                <p><strong>ชื่อเรื่อง:</strong> <?= $displayEmpty($item['name']) ?></p>
                                <p><strong>ชื่อวิทยาศาสตร์:</strong> <?= $displayEmpty($item['scientific_name']) ?></p>
                                <p><strong>พิกัด:</strong> <?= $displayEmpty($item['coord']) ?></p>
                            </div>
                        </div>

                        <div class="full-view-content" style="margin-top: 15px; border-top: 1px dashed #eee; padding-top: 10px;">
                            <div class="row">
                                <div class="col-sm-6">
                                    <p><strong>ชื่ออื่น:</strong> <?= $displayEmpty($item['other_name']) ?></p>
                                    <p><strong>ชื่อสามัญ:</strong> <?= $displayEmpty($item['common_name']) ?></p>
                                    <p><strong>ชื่อวงศ์:</strong> <?= $displayEmpty($item['family_name']) ?></p>
                                    <p><strong>ภูมิภาค:</strong> <?= $displayEmpty($item['region']) ?></p>
                                    <p><strong>จังหวัด:</strong> <?= $displayEmpty($item['province']) ?></p>
                                    <p><strong>อำเภอ:</strong> <?= $displayEmpty($item['district']) ?></p>
                                    <p><strong>ตำบล:</strong> <?= $displayEmpty($item['subdistrict']) ?></p>
                                    <p><strong>รหัสไปรษณีย์:</strong> <?= $displayEmpty($item['zipcode']) ?></p>

                                    <p><strong>ลักษณะ/คุณสมบัติ:</strong> <br><small class="text-muted"><?= $item['characteristics'] ? Html::encode($item['characteristics']) : '<span class="text-muted">(ไม่ระบุ)</span>' ?></small></p>
                                    <p><strong>ประโยชน์:</strong> <br><small class="text-muted"><?= $item['benefits'] ? Html::encode($item['benefits']) : '<span class="text-muted">(ไม่ระบุ)</span>' ?></small></p>
                                    <p><strong>แหล่งที่พบ:</strong> <?= $displayEmpty($item['found_source']) ?></p>
                                    <p><strong>ข้อมูลอื่น ๆ ที่ฉันรู้:</strong> <br><small class="text-muted"><?= $item['other_information'] ? Html::encode($item['other_information']) : '<span class="text-muted">(ไม่ระบุ)</span>' ?></small></p>
                                    <p><strong>หมายเหตุ:</strong> <?= $displayEmpty($item['note']) ?></p>
                                    <p><strong>การแสดงผล:</strong> <?= (isset($item['is_hidden']) && $item['is_hidden'] == 1) ? 'ซ่อน' : 'แสดง' ?></p>
                                </div>
                                <div class="col-sm-6">
                                    <p><strong>ฤดูกาลที่ใช้ประโยชน์:</strong> <?= $displayEmpty($item['season']) ?></p>
                                    <p><strong>ศักยภาพการใช้งาน:</strong> <?= $displayEmpty($item['ability']) ?></p>
                                    <p><strong>สถานะ:</strong> <?= $displayEmpty($item['status']) ?></p>
                                    <p><strong>สัญญาอนุญาต:</strong><br>
                                        <?php if (!empty($item['license_name'])): ?>
                                            <span class="text-primary" style="font-weight: bold;"><?= Html::encode($item['license_name']) ?></span>
                                            (<?= Html::encode($item['license_code']) ?>)
                                            <?php if (!empty($item['license_description'])): ?>
                                                <br><small class="text-muted"><?= Html::encode($item['license_description']) ?></small>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <?= $displayEmpty($item['license_code']) ?>
                                        <?php endif; ?>
                                    </p>
                                   
                                    <p><strong>แหล่งที่มาของข้อมูล:</strong> <br>
                                        <?php if (!empty($item['data_sources_data'])): ?>
                                            <ul style="padding-left: 20px; margin-bottom: 0;">
                                                <?php foreach ($item['data_sources_data'] as $source): ?>
                                                    <li><?php echo Formatter::getDisplaySourceLabelStatic($source['source_name'], $source['author'], $source['published_date'], $source['reference_url']); ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php endif; ?>
                                    </p>
                                    <p><strong>แหล่งที่มาของภาพ:</strong> <br>
                                        <?php if (!empty($item['image_sources_data'])): ?>
                                            <ul style="padding-left: 20px; margin-bottom: 0;">
                                                <?php foreach ($item['image_sources_data'] as $source): ?>
                                                    <li><?php echo Formatter::getDisplaySourceLabelStatic($source['source_name'], $source['author'], $source['published_date'], $source['reference_url']); ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php endif; ?>
                                    </p>
                                    <p><strong>รูปประกอบ:</strong> <br>
                                        <?php if (!empty($item['files'])): ?>
                                            <div style="display: flex; flex-wrap: wrap; gap: 5px; margin-top: 5px;">
                                                <?php foreach ($item['files'] as $filePath): ?>
                                                    <?= Html::img($urlFrontend . $filePath, [
                                                        'title' => basename($filePath),
                                                        'style' => 'width: 50px; height: 50px; object-fit: cover; border-radius: 4px; border: 1px solid #ddd;',
                                                        'class' => 'img-thumbnail'
                                                    ]) ?>
                                                <?php endforeach; ?>
                                            </div>
                                            <small class="text-muted"><?= Html::encode($item['illustration_labels']) ?></small>
                                        <?php else: ?>
                                            <span class="text-muted">(ไม่ระบุ)</span>
                                        <?php endif; ?>
                                    </p>
                                    <p><strong>คำสำคัญ (Tags):</strong> <br>
                                        <?php if (!empty($item['taxonomy_names'])): ?>
                                            <div style="display: flex; flex-wrap: wrap; gap: 4px; margin-top: 5px;">
                                                <?php foreach (explode(',', $item['taxonomy_names']) as $tag): ?>
                                                    <?php if (trim($tag)): ?>
                                                        <span class="label label-default" style="font-weight: normal;"><?= Html::encode(trim($tag)) ?></span>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">(ไม่ระบุ)</span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <?php if ($hasError): ?>
                            <div class="error-box" style="margin-top: 10px; background: #fdf2f2; padding: 10px; border-radius: 4px; border-left: 3px solid #d9534f;">
                                <ul class="text-danger" style="margin:0; padding-left: 20px;">
                                    <?php foreach ($errors as $error): ?>
                                        <li><small><?= Html::encode($error) ?></small></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
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
