<?php

use yii\helpers\Html;
use yii\helpers\Url;
use backend\components\ContentAsyncExportService;

$this->title = 'ไฟล์ Export ของฉัน';
$this->params['breadcrumbs'][] = $this->title;

$typeNames = [
    'content_plant' => 'พืช',
    'content_animal' => 'สัตว์',
    'content_fungi' => 'จุลินทรีย์',
    'content_product' => 'ผลิตภัณฑ์',
    'content_expert' => 'ภูมิปัญญา',
    'content_ecotourism' => 'แหล่งท่องเที่ยว',
];

$statusLabels = [
    ContentAsyncExportService::STATUS_PENDING => '<span class="label label-default">รอดำเนินการ</span>',
    ContentAsyncExportService::STATUS_PROCESSING => '<span class="label label-warning">กำลังประมวลผล</span>',
    ContentAsyncExportService::STATUS_COMPLETED => '<span class="label label-success">เสร็จสมบูรณ์</span>',
    ContentAsyncExportService::STATUS_FAILED => '<span class="label label-danger">ล้มเหลว</span>',
];
?>

<div class="export-downloads-index">
    <h1><?= Html::encode($this->title) ?></h1>

    <div class="alert alert-info">
        <i class="fa fa-info-circle"></i> 
        ไฟล์ Export จะถูกเก็บไว้ 48 ชั่วโมง หลังจากนั้นจะถูกลบอัตโนมัติ
    </div>

    <?php if (empty($jobs)): ?>
        <div class="alert alert-warning">
            <i class="fa fa-exclamation-triangle"></i> 
            ยังไม่มีไฟล์ Export
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>ประเภท</th>
                        <th>ชื่อไฟล์</th>
                        <th>จำนวนข้อมูล</th>
                        <th>สถานะ</th>
                        <th>วันที่สร้าง</th>
                        <th>วันหมดอายุ</th>
                        <th>การดำเนินการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($jobs as $job): ?>
                        <?php
                        $typeName = isset($typeNames[$job['type_key']]) ? $typeNames[$job['type_key']] : 'ข้อมูล';
                        $status = $job['status'];
                        $isExpired = isset($job['expires_at']) && strtotime($job['expires_at']) < time();
                        $canDownload = $status === ContentAsyncExportService::STATUS_COMPLETED && !$isExpired;
                        ?>
                        <tr>
                            <td><?= Html::encode($typeName) ?></td>
                            <td>
                                <?php if (!empty($job['zip_file_name'])): ?>
                                    <?= Html::encode($job['zip_file_name']) ?>
                                <?php else: ?>
                                    <em class="text-muted">-</em>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($job['total_rows'] > 0): ?>
                                    <?= number_format($job['total_rows']) ?> รายการ
                                <?php else: ?>
                                    <em class="text-muted">-</em>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= $statusLabels[$status] ?? '<span class="label label-default">ไม่ทราบ</span>' ?>
                                <?php if ($isExpired): ?>
                                    <br><small class="text-danger">หมดอายุแล้ว</small>
                                <?php endif; ?>
                                <?php if ($status === ContentAsyncExportService::STATUS_PROCESSING): ?>
                                    <br><small class="text-muted"><?= Html::encode($job['progress_message']) ?></small>
                                <?php endif; ?>
                                <?php if ($status === ContentAsyncExportService::STATUS_FAILED && !empty($job['error_message'])): ?>
                                    <br><small class="text-danger"><?= Html::encode($job['error_message']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= date('d/m/Y H:i', strtotime($job['created_at'])) ?>
                            </td>
                            <td>
                                <?php if (isset($job['expires_at'])): ?>
                                    <?= date('d/m/Y H:i', strtotime($job['expires_at'])) ?>
                                    <?php if (!$isExpired): ?>
                                        <?php
                                        $hoursLeft = (strtotime($job['expires_at']) - time()) / 3600;
                                        if ($hoursLeft < 24) {
                                            echo '<br><small class="text-warning">เหลือ ' . round($hoursLeft, 1) . ' ชม.</small>';
                                        }
                                        ?>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <em class="text-muted">-</em>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($canDownload): ?>
                                    <?= Html::a(
                                        '<i class="fa fa-download"></i> ดาวน์โหลด',
                                        ['download', 'jobId' => $job['id']],
                                        ['class' => 'btn btn-success btn-sm']
                                    ) ?>
                                <?php endif; ?>
                                
                                <?= Html::button(
                                    '<i class="fa fa-trash"></i> ลบ',
                                    [
                                        'class' => 'btn btn-danger btn-sm delete-export-btn',
                                        'data-job-id' => $job['id'],
                                        'data-file-name' => $job['zip_file_name'] ?? 'ไฟล์นี้'
                                    ]
                                ) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php
$deleteUrl = Url::to(['delete']);
$this->registerJs(<<<JS
$('.delete-export-btn').on('click', function() {
    var jobId = $(this).data('job-id');
    var fileName = $(this).data('file-name');
    var btn = $(this);
    
    if (!confirm('คุณต้องการลบไฟล์ "' + fileName + '" หรือไม่?')) {
        return;
    }
    
    btn.prop('disabled', true);
    
    $.ajax({
        url: '{$deleteUrl}',
        type: 'POST',
        dataType: 'json',
        data: {
            jobId: jobId,
            _csrf: yii.getCsrfToken()
        },
        success: function(response) {
            if (response.status === 'success') {
                alert(response.message);
                location.reload();
            } else {
                alert(response.message || 'เกิดข้อผิดพลาด');
                btn.prop('disabled', false);
            }
        },
        error: function() {
            alert('เกิดข้อผิดพลาดในการลบไฟล์');
            btn.prop('disabled', false);
        }
    });
});
JS
);
?>
