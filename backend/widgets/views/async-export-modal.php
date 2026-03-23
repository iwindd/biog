<?php
/**
 * Async Export Modal Template
 * 
 * @var string $contentType
 * @var string $modalTitle
 */

use yii\helpers\Html;

$modalId = $contentType . 'ExportModal';
$prefix = $contentType;
?>

<div class="modal fade" id="<?= $modalId ?>" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title"><?= Html::encode($modalTitle) ?></h4>
            </div>
            <div class="modal-body">
                <div id="<?= $prefix ?>ExportFormState">
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="<?= $prefix ?>ExportDateFrom">วันที่เริ่มต้น</label>
                                <input type="date" class="form-control" id="<?= $prefix ?>ExportDateFrom">
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="<?= $prefix ?>ExportDateTo">วันที่สิ้นสุด</label>
                                <input type="date" class="form-control" id="<?= $prefix ?>ExportDateTo">
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-info" style="margin-bottom: 10px;">
                        <i class="fa fa-info-circle"></i> ระบบจะทำงานเบื้องหลัง คุณสามารถปิดหน้าต่างนี้ได้<br>
                        <small>
                            - ระบบจะส่งอีเมลแจ้งเตือนเมื่อ export เสร็จ พร้อมลิงก์ดาวน์โหลด<br>
                            - ไฟล์จะเก็บไว้ 48 ชั่วโมง และสามารถดูย้อนหลังได้ที่ <a href="/admin/export-downloads" target="_blank">ไฟล์ Export ของฉัน</a>
                        </small>
                    </div>
                    <div id="<?= $prefix ?>ExportStatusBox" class="alert alert-warning" style="display:none; margin-bottom: 0;"></div>
                </div>
                
                <div id="<?= $prefix ?>ExportSuccessState" style="display: none;">
                    <div class="text-center" style="padding: 20px 0;">
                        <div style="font-size: 48px; color: #28a745; margin-bottom: 20px;">
                            <i class="fa fa-check-circle"></i>
                        </div>
                        <h4 style="color: #28a745; margin-bottom: 15px;">กำลังดำเนินการ!</h4>
                        <p style="font-size: 16px; color: #6c757d; margin-bottom: 15px;">
                            ระบบกำลังสร้างไฟล์ Export ของคุณในเบื้องหลัง<br>
                            คุณสามารถปิดหน้าต่างนี้ได้<br>
                            เราจะแจ้งเตือนทางอีเมลเมื่อไฟล์พร้อมดาวน์โหลด
                        </p>
                        <div class="alert alert-success" style="margin-bottom: 0;">
                            <i class="fa fa-envelope"></i> อีเมลแจ้งเตือนจะถูกส่งไปยังอีเมลของคุณเมื่อ Export เสร็จสมบูรณ์
                        </div>
                        <div style="margin-top: 20px;">
                            <small id="<?= $prefix ?>ExportProgressText" class="text-muted">กำลังเริ่มต้นการ Export...</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div id="<?= $prefix ?>ExportInitialFooter">
                    <button type="button" class="btn btn-default" data-dismiss="modal" id="<?= $prefix ?>ExportCancelBtn">ปิด</button>
                    <button type="button" class="btn btn-primary" id="<?= $prefix ?>ExportSubmitBtn">เริ่ม Export</button>
                </div>
                
                <div id="<?= $prefix ?>ExportSuccessFooter" style="display: none;">
                    <button type="button" class="btn btn-success" data-dismiss="modal" id="<?= $prefix ?>ExportSuccessCloseBtn">ปิด</button>
                </div>
            </div>
        </div>
    </div>
</div>
