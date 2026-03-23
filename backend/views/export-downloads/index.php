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
                        $canCancel = in_array($status, [ContentAsyncExportService::STATUS_PENDING, ContentAsyncExportService::STATUS_PROCESSING]);
                        $canDelete = $status === ContentAsyncExportService::STATUS_COMPLETED && !empty($job['zip_file_name']) && !empty($job['zip_path']);
                        ?>
                        <tr data-job-id="<?= Html::encode($job['id']) ?>" data-status="<?= Html::encode($status) ?>">
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
                                
                                <?php if ($canCancel): ?>
                                    <?= Html::button(
                                        '<i class="fa fa-times"></i> ยกเลิก',
                                        [
                                            'class' => 'btn btn-warning btn-sm cancel-export-btn',
                                            'data-job-id' => $job['id'],
                                            'data-type-name' => $typeName
                                        ]
                                    ) ?>
                                <?php endif; ?>
                                
                                <?php if ($canDelete): ?>
                                    <?= Html::button(
                                        '<i class="fa fa-trash"></i> ลบ',
                                        [
                                            'class' => 'btn btn-danger btn-sm delete-export-btn',
                                            'data-job-id' => $job['id'],
                                            'data-file-name' => $job['zip_file_name'] ?? 'ไฟล์นี้'
                                        ]
                                    ) ?>
                                <?php endif; ?>
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
$cancelUrl = Url::to(['cancel']);
$statusUrl = Url::to(['/export/status']);

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
        url: '{$deleteUrl}?jobId=' + encodeURIComponent(jobId),
        type: 'POST',
        dataType: 'json',
        data: {
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

$('.cancel-export-btn').on('click', function() {
    var jobId = $(this).data('job-id');
    var typeName = $(this).data('type-name');
    var btn = $(this);
    
    if (!confirm(`คุณต้องการยกเลิกการ Export ${typeName} หรือไม่?\n\nหมายเหตุ: การยกเลิกจะหยุดการประมวลผลทันที`)) {
        return;
    }
    
    btn.prop('disabled', true);
    
    $.ajax({
        url: '{$cancelUrl}?jobId=' + encodeURIComponent(jobId),
        type: 'POST',
        dataType: 'json',
        data: {
            _csrf: yii.getCsrfToken()
        },
        success: function(response) {
            if (response.status === 'success') {
                alert(response.message);
                location.reload();
            } else {
                alert(response.message || 'ไม่สามารถยกเลิกการ Export ได้');
                btn.prop('disabled', false);
            }
        },
        error: function() {
            alert('เกิดข้อผิดพลาดในการยกเลิก Export');
            btn.prop('disabled', false);
        }
    });
});

// Sequential polling for pending/processing jobs
var statusLabels = {
    'pending': '<span class="label label-default">รอดำเนินการ</span>',
    'processing': '<span class="label label-warning">กำลังประมวลผล</span>',
    'completed': '<span class="label label-success">เสร็จสมบูรณ์</span>',
    'failed': '<span class="label label-danger">ล้มเหลว</span>'
};

function pollJobSequentially(jobQueue, index) {
    if (index >= jobQueue.length) {
        // All jobs processed, check if any are still active
        var activeRows = $('tr[data-status="pending"], tr[data-status="processing"]');
        if (activeRows.length === 0) {
            console.log('All jobs completed, stopping polling');
            location.reload();
            return;
        } else {
            // Some jobs still active, rebuild queue and continue
            var newQueue = [];
            activeRows.each(function() {
                newQueue.push({
                    jobId: $(this).data('job-id'),
                    row: $(this)
                });
            });
            setTimeout(function() {
                pollJobSequentially(newQueue, 0);
            }, 1000);
            return;
        }
    }
    
    var currentJob = jobQueue[index];
    var jobId = currentJob.jobId;
    var row = currentJob.row;
    
    $.ajax({
        url: '{$statusUrl}',
        method: 'GET',
        data: { jobId: jobId },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success' && response.job) {
                var job = response.job;
                var state = job.state;
                var cols = row.find('td');
                
                // Update status column (index 3)
                var statusHtml = statusLabels[state] || statusLabels['pending'];
                if (state === 'processing') {
                    statusHtml += '<br><small class="text-muted">' + (job.progressMessage || 'กำลังดำเนินการ...') + '</small>';
                } else if (state === 'failed' && job.errorMessage) {
                    statusHtml += '<br><small class="text-danger">' + job.errorMessage + '</small>';
                }
                cols.eq(3).html(statusHtml);
                
                // Update row data-status
                row.attr('data-status', state);
                
                // If job completed/failed, move to next job immediately
                if (state === 'completed' || state === 'failed') {
                    console.log('Job ' + jobId + ' completed with status: ' + state + ', moving to next job');
                    setTimeout(function() {
                        pollJobSequentially(jobQueue, index + 1);
                    }, 5000); // Small delay before next job
                } else {
                    // Job still processing, continue polling this job
                    setTimeout(function() {
                        pollJobSequentially(jobQueue, index);
                    }, 5000); // Poll same job every 2 seconds while processing
                }
            } else {
                // Error response, move to next job
                setTimeout(function() {
                    pollJobSequentially(jobQueue, index + 1);
                }, 5000);
            }
        },
        error: function() {
            // Network error, move to next job
            setTimeout(function() {
                pollJobSequentially(jobQueue, index + 1);
            }, 5000);
        }
    });
}

function startSequentialPolling() {
    var activeRows = $('tr[data-status="pending"], tr[data-status="processing"]');
    if (activeRows.length === 0) {
        console.log('No active jobs to poll');
        return;
    }
    
    var jobQueue = [];
    activeRows.each(function() {
        jobQueue.push({
            jobId: $(this).data('job-id'),
            row: $(this)
        });
    });
    
    console.log('Starting sequential polling for ' + jobQueue.length + ' jobs');
    pollJobSequentially(jobQueue, 0);
}

// Start polling if there are active jobs
if ($('tr[data-status="pending"], tr[data-status="processing"]').length > 0) {
    console.log('Found active jobs, starting sequential polling');
    startSequentialPolling();
}
JS
);
?>
