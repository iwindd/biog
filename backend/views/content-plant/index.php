<?php

use backend\components\BackendHelper;
use common\components\Upload;
use kartik\daterange\DateRangePicker;
use kartik\select2\Select2;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;

$listUser = \yii\helpers\Url::to(['/api/userslist']);
$listEditUser = \yii\helpers\Url::to(['/api/editslist']);
$listApprovedUser = \yii\helpers\Url::to(['/api/approverlist']);
$startExportUrl = Url::to(['/content-plant/start-export']);
$exportStatusUrl = Url::to(['/content-plant/export-status']);
$contentPlantViewBaseUrl = Url::to(['/content-plant']);

$total = $dataProvider->totalCount;  // total records // 15


use kartik\date\DatePicker;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\ContentPlantSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'จัดการข้อมูลพืช';
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="content-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('เพิ่มข้อมูลพืช', ['create'], ['class' => 'btn btn-success']) ?>
        <?= Html::a('Import Excel', ['import'], ['class' => 'btn btn-primary']) ?>
        <?= Html::button(Html::img('/images/csv.png', ['class' => 'csv-export']) . 'Export ข้อมูลพืช', ['class' => 'btn btn-info export-bakcground f-right', 'title' => 'Export Excel', 'id' => 'openPlantExportModal']) ?>
    
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>
    <div class="table-responsive">
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'rowOptions' => function ($model, $key, $index, $grid) {
            return ['data-id' => $model->id];
        },
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            [
                'format' => 'raw',
                'attribute' => 'picture_path',
                'filter' => false,
                'value' => function ($model) {
                    $image = Upload::readfilePictureNoPermission('content-plant', $model->picture_path);
                    if (empty($image)) {
                        return '<img width="100%" src="/admin/images/BIOG_default_plant.png" />';
                    }
                    return $image;
                }
            ],
            'name',
            [
                'label' => 'จำนวนผู้เข้าชม',
                'format' => 'raw',
                'filter' => false,
                'value' => function ($model) {
                    if ($model->content_root_id == 0) {
                        $model->content_root_id = $model->id;
                    }
                    return BackendHelper::getPageview($model->content_root_id);
                }
            ],
            // 'description:ntext',
            [
                'attribute' => 'created_by_user_id',
                'format' => 'raw',
                'filter' => Select2::widget([
                    'model' => $searchModel,
                    'attribute' => 'created_by_user_id',
                    'initValueText' => BackendHelper::getName($searchModel->created_by_user_id),
                    'options' => [
                        'placeholder' => '',
                    ],
                    'pluginOptions' => [
                        'minimumInputLength' => 2,
                        'allowClear' => true,
                        'tags' => true,
                        'language' => [
                            'errorLoading' => new JsExpression("function () { return 'Waiting for results...'; }"),
                        ],
                        'ajax' => [
                            'url' => $listUser,
                            'dataType' => 'json',
                            'data' => new JsExpression('function(params) { return {q:params.term}; }')
                        ],
                        'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                        'templateResult' => new JsExpression('function(taxonomy) { return taxonomy.text; }'),
                        'templateSelection' => new JsExpression('function (taxonomy) { return taxonomy.text; }'),
                    ],
                ]),
                'value' => function ($model) {
                    return BackendHelper::getName($model->created_by_user_id);
                }
            ],
            [
                'attribute' => 'updated_by_user_id',
                'format' => 'raw',
                'filter' => Select2::widget([
                    'model' => $searchModel,
                    'attribute' => 'updated_by_user_id',
                    'initValueText' => BackendHelper::getName($searchModel->updated_by_user_id),
                    'options' => [
                        'placeholder' => '',
                    ],
                    'pluginOptions' => [
                        'minimumInputLength' => 2,
                        'allowClear' => true,
                        'tags' => true,
                        'language' => [
                            'errorLoading' => new JsExpression("function () { return 'Waiting for results...'; }"),
                        ],
                        'ajax' => [
                            'url' => $listEditUser,
                            'dataType' => 'json',
                            'data' => new JsExpression('function(params) { return {q:params.term}; }')
                        ],
                        'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                        'templateResult' => new JsExpression('function(taxonomy) { return taxonomy.text; }'),
                        'templateSelection' => new JsExpression('function (taxonomy) { return taxonomy.text; }'),
                    ],
                ]),
                'value' => function ($model) {
                    return BackendHelper::getName($model->updated_by_user_id);
                }
            ],
            [
                'attribute' => 'approved_by_user_id',
                'format' => 'raw',
                'filter' => Select2::widget([
                    'model' => $searchModel,
                    'attribute' => 'approved_by_user_id',
                    'initValueText' => BackendHelper::getName($searchModel->approved_by_user_id),
                    'options' => [
                        'placeholder' => '',
                    ],
                    'pluginOptions' => [
                        'minimumInputLength' => 2,
                        'allowClear' => true,
                        'tags' => true,
                        'language' => [
                            'errorLoading' => new JsExpression("function () { return 'Waiting for results...'; }"),
                        ],
                        'ajax' => [
                            'url' => $listApprovedUser,
                            'dataType' => 'json',
                            'data' => new JsExpression('function(params) { return {q:params.term}; }')
                        ],
                        'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                        'templateResult' => new JsExpression('function(taxonomy) { return taxonomy.text; }'),
                        'templateSelection' => new JsExpression('function (taxonomy) { return taxonomy.text; }'),
                    ],
                ]),
                'value' => function ($model) {
                    return BackendHelper::getName($model->approved_by_user_id);
                }
            ],
            [
                'attribute' => 'note',
            ],
            [
                'format' => 'html',
                'attribute' => 'status',
                'filter' => Html::activeDropDownList($searchModel, 'status', array('pending' => 'รอตรวจสอบ', 'approved' => 'อนุมัติแล้ว', 'rejected' => 'ไม่อนุมัติ'), ['class' => 'form-control', 'prompt' => 'ทั้งหมด']),
                'value' => function ($model) {
                    return \backend\components\BackendHelper::getStatusBadge($model->status);
                }
            ],
            [
                'format' => 'html',
                'attribute' => 'is_hidden',
                'filter' => Html::activeDropDownList($searchModel, 'is_hidden', array('0' => 'แสดงผล', '1' => 'ซ่อน'), ['class' => 'form-control', 'prompt' => 'ทั้งหมด']),
                'value' => function ($model) {
                    if ($model->is_hidden == '0') {
                        return "<span class='label label-success'>แสดงผล</span>";
                    } elseif ($model->is_hidden == '1') {
                        return "<span class='label label-warning'>ซ่อน</span>";
                    }
                }
            ],
            [
                'label' => 'วันที่แก้ไขล่าสุด',
                'attribute' => 'updated_at',
                'filter' => DateRangePicker::widget([
                    'model' => $searchModel,
                    'attribute' => 'updated_at',
                    'convertFormat' => true,
                    // 'useWithAddon'=>true,
                    'pluginOptions' => [
                        'locale' => [
                            'format' => 'Y-m-d',
                            'separator' => ' to ',
                        ],
                        'opens' => 'left'
                    ]
                ]),
                'value' => function ($model, $key, $index, $column) {
                    return $model->updated_at;
                }
            ],
            // 'other_information:ntext',
            // 'source_information:ntext',
            // 'latitude',
            // 'longitude',
            // 'region_id',
            // 'province_id',
            // 'district_id',
            // 'subdistrict_id',
            // 'zipcode_id',
            // 'approved_by_user_id',
            // 'created_by_user_id',
            // 'updated_by_user_id',
            // 'active',
            // 'created_at',
            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
    </div>
</div>

<div class="modal fade" id="plantExportModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Export ข้อมูลพืช</h4>
            </div>
            <div class="modal-body">
                <!-- Initial Form State -->
                <div id="plantExportFormState">
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="plantExportDateFrom">วันที่เริ่มต้น</label>
                                <input type="date" class="form-control" id="plantExportDateFrom">
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="plantExportDateTo">วันที่สิ้นสุด</label>
                                <input type="date" class="form-control" id="plantExportDateTo">
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-info" style="margin-bottom: 10px;">
                        <i class="fa fa-info-circle"></i> ระบบจะทำงานเบื้องหลัง คุณสามารถปิดหน้าต่างนี้ได้<br>
                        <small>- ระบบจะส่งอีเมลแจ้งเตือนเมื่อ export เสร็จ พร้อมลิงก์ดาวน์โหลด<br>
                        - ไฟล์จะเก็บไว้ 48 ชั่วโมง และสามารถดูย้อนหลังได้ที่ <a href="/admin/export-downloads" target="_blank">ไฟล์ Export ของฉัน</a></small>
                    </div>
                    <div id="plantExportStatusBox" class="alert alert-warning" style="display:none; margin-bottom: 0;"></div>
                </div>
                
                <!-- Success State (Hidden initially) -->
                <div id="plantExportSuccessState" style="display: none;">
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
                        <!-- Progress Bar -->
                        <div style="margin-top: 20px;">
                            <small id="plantExportProgressText" class="text-muted">กำลังเริ่มต้นการ Export...</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <!-- Initial Footer -->
                <div id="plantExportInitialFooter">
                    <button type="button" class="btn btn-default" data-dismiss="modal" id="plantExportCancelBtn">ปิด</button>
                    <button type="button" class="btn btn-primary" id="plantExportSubmitBtn">เริ่ม Export</button>
                </div>
                
                <!-- Success Footer (Hidden initially) -->
                <div id="plantExportSuccessFooter" style="display: none;">
                    <button type="button" class="btn btn-success" data-dismiss="modal" id="plantExportSuccessCloseBtn">ปิด</button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php

$this->registerJs(<<<JS
var plantExportPollTimer = null;
var plantStartExportUrl = '{$startExportUrl}';
var plantExportStatusUrl = '{$exportStatusUrl}';
var contentPlantViewBaseUrl = '{$contentPlantViewBaseUrl}';

function setPlantExportStatus(message, type) {
    var statusBox = $('#plantExportStatusBox');
    statusBox.removeClass('alert-warning alert-danger alert-success alert-info');
    statusBox.addClass(type || 'alert-info');
    statusBox.text(message);
    statusBox.show();
}

function togglePlantExportLoading(isLoading) {
    var submitBtn = $('#plantExportSubmitBtn');
    var cancelBtn = $('#plantExportCancelBtn');
    
    if (isLoading) {
        submitBtn.prop('disabled', true);
        submitBtn.html('<i class="fa fa-spinner fa-spin"></i> กำลังดำเนินการ...');
        cancelBtn.prop('disabled', true);
    } else {
        submitBtn.prop('disabled', false);
        submitBtn.html('เริ่ม Export');
        cancelBtn.prop('disabled', false);
    }
}

function showPlantExportSuccessState() {
    // Hide form state and show success state
    $('#plantExportFormState').hide();
    $('#plantExportSuccessState').show();
    
    // Hide initial footer and show success footer
    $('#plantExportInitialFooter').hide();
    $('#plantExportSuccessFooter').show();
    
    // Hide status box
    $('#plantExportStatusBox').hide();
}

function resetPlantExportModal() {
    // Show form state and hide success state
    $('#plantExportFormState').show();
    $('#plantExportSuccessState').hide();
    
    // Show initial footer and hide success footer
    $('#plantExportInitialFooter').show();
    $('#plantExportSuccessFooter').hide();
    
    // Clear form inputs
    $('#plantExportDateFrom').val('');
    $('#plantExportDateTo').val('');
    
    // Clear status and reset loading state
    $('#plantExportStatusBox').hide().text('');
    togglePlantExportLoading(false);
}

function updatePlantExportProgressBar(progress, message) {
    var progressText = $('#plantExportProgressText');
    
    // Update progress message
    progressText.text(message);
}

function pollPlantExport(jobId) {
    $.ajax({
        url: plantExportStatusUrl,
        type: 'GET',
        dataType: 'json',
        data: {jobId: jobId},
        success: function (response) {
            if (!response || response.status !== 'success' || !response.job) {
                // Don't show error in success state - just stop polling
                clearTimeout(plantExportPollTimer);
                return;
            }

            // Update progress bar if in success state
            if ($('#plantExportSuccessState').is(':visible')) {
                updatePlantExportProgressBar(response.job.progress || 0, response.job.progressMessage || 'กำลังดำเนินการ...');
            }

            // Only update status if we're still in form state (not success state)
            if ($('#plantExportFormState').is(':visible')) {
                setPlantExportStatus(response.job.progressMessage, response.job.state === 'failed' ? 'alert-danger' : 'alert-info');
            }

            if (response.job.state === 'completed' && response.job.downloadReady) {
                clearTimeout(plantExportPollTimer);
                // Update progress bar to 100% when completed
                if ($('#plantExportSuccessState').is(':visible')) {
                    updatePlantExportProgressBar(100, 'Export เสร็จสมบูรณ์! กำลังดาวน์โหลด...');
                }

                // If still in form state, show success state
                if ($('#plantExportFormState').is(':visible')) {
                    showPlantExportSuccessState();
                }
                // Auto-download and close modal
                window.location.href = response.job.downloadUrl;
                setTimeout(function () {
                    $('#plantExportModal').modal('hide');
                }, 800);
                return;
            }

            if (response.job.state === 'failed') {
                clearTimeout(plantExportPollTimer);
                // Update progress bar to show failed state
                if ($('#plantExportSuccessState').is(':visible')) {
                    updatePlantExportProgressBar(0, 'Export ล้มเหลว!');
                }
                // Only show error if still in form state
                if ($('#plantExportFormState').is(':visible')) {
                    if (response.job.errorMessage) {
                        setPlantExportStatus(response.job.errorMessage, 'alert-danger');
                    }
                    togglePlantExportLoading(false);
                }
                return;
            }

            plantExportPollTimer = setTimeout(function () {
                pollPlantExport(jobId);
            }, 1000);
        },
        error: function () {
            // Don't show error in success state - just stop polling
            clearTimeout(plantExportPollTimer);
            // Only reset if still in form state
            if ($('#plantExportFormState').is(':visible')) {
                setPlantExportStatus('เกิดข้อผิดพลาดระหว่างตรวจสอบสถานะ export', 'alert-danger');
                togglePlantExportLoading(false);
            }
        }
    });
}

$('#openPlantExportModal').on('click', function () {
    // Reset modal to initial state before showing
    resetPlantExportModal();
    $('#plantExportModal').modal('show');
});

$('#plantExportSubmitBtn').on('click', function () {
    var dateFrom = $('#plantExportDateFrom').val();
    var dateTo = $('#plantExportDateTo').val();

    if (!dateFrom || !dateTo) {
        setPlantExportStatus('กรุณาเลือกช่วงวันที่ให้ครบถ้วน', 'alert-danger');
        return;
    }

    if (dateFrom > dateTo) {
        setPlantExportStatus('วันที่เริ่มต้นต้องไม่มากกว่าวันที่สิ้นสุด', 'alert-danger');
        return;
    }

    togglePlantExportLoading(true);
    setPlantExportStatus('กำลังเริ่มต้น export', 'alert-info');

    var requestData = {date_from: dateFrom, date_to: dateTo};
    if (window.yii) {
        requestData[yii.getCsrfParam()] = yii.getCsrfToken();
    }

    $.ajax({
        url: plantStartExportUrl + window.location.search,
        type: 'POST',
        dataType: 'json',
        data: requestData,
        success: function (response) {
            if (!response || response.status !== 'success' || !response.jobId) {
                setPlantExportStatus((response && response.message) ? response.message : 'ไม่สามารถเริ่ม export ได้', 'alert-danger');
                togglePlantExportLoading(false);
                return;
            }

            // Show success state immediately after successful submission
            showPlantExportSuccessState();
            
            // Start polling for completion (but modal is now in success state)
            pollPlantExport(response.jobId);
        },
        error: function () {
            setPlantExportStatus('เกิดข้อผิดพลาดระหว่างเริ่ม export', 'alert-danger');
            togglePlantExportLoading(false);
        }
    });
});

$('#plantExportSuccessCloseBtn').on('click', function () {
    // Explicitly stop polling when closing from success state
    clearTimeout(plantExportPollTimer);
});

$('#plantExportModal').on('hidden.bs.modal', function () {
    clearTimeout(plantExportPollTimer);
    // Reset modal when closed so it's ready for next use
    resetPlantExportModal();
});

$('td').click(function (e) {
    var id = $(this).closest('tr').data('id');
    if (id) {
        if (e.target == this) {
            location.href = contentPlantViewBaseUrl + '/' + id;
        }
    }
});
JS
);
?>
