<?php
/**
 * Shared partial for import summary card
 * 
 * @var array $item The import item data
 * @var int $index The item index
 * @var array $errors Validation errors from ImportHelper::validateImportItem()
 * @var string $urlFrontend Frontend URL prefix
 */

use yii\helpers\Html;
use common\components\Formatter;

$hasError = !empty($errors);
$displayEmpty = function($value) {
    return (!empty($value) || $value === 0 || $value === '0') ? Html::encode($value) : '<span class="text-muted">(ไม่ระบุ)</span>';
};
?>
<div class="col-md-12 import-item-card" data-error="<?= $hasError ? '1' : '0' ?>">
    <div class="panel <?= $hasError ? 'panel-warning' : 'panel-success' ?>" style="box-shadow: 0 2px 5px rgba(0,0,0,0.1); border-radius: 8px; overflow: hidden; margin-bottom: 20px;">
        <div class="panel-heading">
            <div class="row">
                <div class="col-sm-8">
                    <h3 class="panel-title" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                        <strong>#<?= $index + 1 ?></strong> — <?= Html::encode($item['name'] ?? 'ไม่มีชื่อ') ?>
                        <?php if ($hasError): ?>
                            <span class="label label-warning"><i class="glyphicon glyphicon-warning-sign"></i> <?= count($errors) ?> ข้อผิดพลาด</span>
                        <?php else: ?>
                            <span class="label label-success"><i class="glyphicon glyphicon-ok"></i> ผ่าน</span>
                        <?php endif; ?>
                    </h3>
                </div>
                <div class="col-sm-4 text-right">
                    <small class="text-muted" style="font-size: 13px;">
                        สถานะ: <strong><?= Html::encode($item['status'] ?? 'pending') ?></strong>
                        | การแสดงผล: <strong><?= ($item['is_hidden'] ?? 0) ? 'ซ่อน' : 'แสดงผล' ?></strong>
                    </small>
                </div>
            </div>
        </div>
        <div class="panel-body">
            <?php if ($hasError): ?>
                <div class="alert alert-danger" style="margin-bottom: 10px; padding: 8px 12px;">
                    <strong><i class="glyphicon glyphicon-exclamation-sign"></i> ข้อผิดพลาด:</strong>
                    <ul style="margin-bottom: 0; padding-left: 20px;">
                        <?php foreach ($errors as $error): ?>
                            <li><?= Html::encode($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

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

        </div>
    </div>
</div>
