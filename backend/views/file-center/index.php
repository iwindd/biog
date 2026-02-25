<?php

use yii\helpers\Html;
use yii\widgets\ListView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'File Center';
$this->params['breadcrumbs'][] = $this->title;

$css = <<<CSS
.file-item {
    border: 1px solid #ddd;
    padding: 10px;
    margin-bottom: 20px;
    text-align: center;
    border-radius: 4px;
    height: 100%;
}
.file-item img {
    max-width: 100%;
    height: 120px;
    object-fit: contain;
    margin-bottom: 10px;
}
.file-item .file-name {
    font-size: 12px;
    word-break: break-all;
    margin-bottom: 5px;
    height: 35px;
    overflow: hidden;
}
.file-item .file-size {
    font-size: 11px;
    color: #888;
}
.file-item-actions {
    margin-top: 10px;
}
#dropzone {
    border: 2px dashed #0087F7;
    border-radius: 5px;
    background: white;
    padding: 20px;
    text-align: center;
    cursor: pointer;
    margin-bottom: 20px;
}
#dropzone.dragover {
    background: #e1f5fe;
}
CSS;
$this->registerCss($css);
?>
<div class="file-center-index box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title"><?= Html::encode($this->title) ?></h3>
    </div>
    <div class="box-body">
        
        <div id="dropzone" onclick="document.getElementById('fileCenterUpload').click()">
            <h4><i class="fa fa-cloud-upload"></i> คลิกที่นี่ หรือลากไฟล์มาวางเพื่ออัปโหลด</h4>
            <p class="text-muted">รองรับรูปภาพและเอกสารต่างๆ</p>
            <input type="file" id="fileCenterUpload" style="display: none;" multiple>
        </div>
        
        <div id="upload-progress" style="display: none; margin-bottom: 20px;">
            <div class="progress">
                <div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%">
                    <span class="sr-only">0% Complete</span>
                </div>
            </div>
        </div>

        <?php Pjax::begin(['id' => 'file-center-grid']); ?>
        <div class="row">
        <?= ListView::widget([
            'dataProvider' => $dataProvider,
            'layout' => "{items}\n<div class='col-md-12 text-center'>{pager}</div>",
            'itemOptions' => ['class' => 'col-md-2 col-sm-3 col-xs-6'],
            'itemView' => function ($model, $key, $index, $widget) {
                $isImage = strpos($model->file_type, 'image/') === 0;
                $urlFrontend = isset(Yii::$app->params['urlFrontend']) ? Yii::$app->params['urlFrontend'] : '';
                $previewUrl = $isImage ? $urlFrontend . $model->file_path : '/images/default.png'; // Should replace with appropriate icon if not image
                
                $html = '<div class="file-item">';
                $html .= '<img src="' . $previewUrl . '" alt="' . Html::encode($model->alt_text) . '" title="' . Html::encode($model->file_name) . '">';
                $html .= '<div class="file-name" title="' . Html::encode($model->file_name) . '">' . Html::encode($model->file_name) . '</div>';
                $html .= '<div class="file-size">' . $model->getFileSizeText() . '</div>';
                $html .= '<div class="file-item-actions">';
                $html .= Html::a('<i class="fa fa-trash"></i>', ['delete', 'id' => $model->id], [
                    'class' => 'btn btn-xs btn-danger',
                    'data' => [
                        'confirm' => 'คุณแน่ใจหรือไม่ที่จะลบไฟล์นี้?',
                        'method' => 'post',
                    ],
                ]);
                $html .= ' ' . Html::button('<i class="fa fa-link"></i> คัดลอกลิงก์', [
                    'class' => 'btn btn-xs btn-default copy-link',
                    'data-url' => $model->file_path,
                ]);
                $html .= '</div>';
                $html .= '</div>';
                
                return $html;
            },
        ]) ?>
        </div>
        <?php Pjax::end(); ?>

    </div>
</div>

<?php
$uploadUrl = yii\helpers\Url::to(['file-center/upload']);
$csrfParam = Yii::$app->request->csrfParam;
$csrfToken = Yii::$app->request->csrfToken;
$urlFrontend = isset(Yii::$app->params['urlFrontend']) ? Yii::$app->params['urlFrontend'] : "";
$uploadLimitsConfig = json_encode(isset(Yii::$app->params['fileCenterUploadLimits']) ? Yii::$app->params['fileCenterUploadLimits'] : []);

$js = <<<JS
$(document).ready(function() {
    var dropzone = document.getElementById('dropzone');
    var fileInput = document.getElementById('fileCenterUpload');
    var progressBarContainer = document.getElementById('upload-progress');
    var progressBar = document.querySelector('.progress-bar');
    
    // Drag events
    dropzone.addEventListener('dragover', function(e) {
        e.preventDefault();
        dropzone.classList.add('dragover');
    });
    
    dropzone.addEventListener('dragleave', function(e) {
        e.preventDefault();
        dropzone.classList.remove('dragover');
    });
    
    dropzone.addEventListener('drop', function(e) {
        e.preventDefault();
        dropzone.classList.remove('dragover');
        if (e.dataTransfer.files.length) {
            handleUpload(e.dataTransfer.files);
        }
    });
    
    // Input change event
    fileInput.addEventListener('change', function() {
        if (this.files.length) {
            handleUpload(this.files);
        }
    });

    // Accept criteria
    var uploadLimitsConfig = $uploadLimitsConfig;
    
    function validateFile(file) {
        var ext = file.name.split('.').pop().toLowerCase();
        var isValidExt = false;
        var maxSize = 0;
        
        for (var category in uploadLimitsConfig) {
            if (uploadLimitsConfig[category].extensions.includes(ext)) {
                isValidExt = true;
                maxSize = uploadLimitsConfig[category].maxSize;
                break;
            }
        }
        
        if (!isValidExt) {
            alert('ประเภทไฟล์ "' + file.name + '" ไม่ได้รับอนุญาตให้ใช้ระะบบเพิ่มไฟล์อัปโหลด');
            return false;
        }
        
        if (file.size > maxSize) {
            alert('ขนาดไฟล์ "' + file.name + '" ใหญ่เกินกำหนด (สูงสุด ' + (maxSize/(1024*1024)) + 'MB)');
            return false;
        }
        return true;
    }
    
    function handleUpload(files) {
        let sortedFiles = Array.from(files).filter(validateFile);
        if (sortedFiles.length === 0) return;
        
        progressBarContainer.style.display = 'block';
        let completed = 0;
        let total = sortedFiles.length;
        
        let uploadPromises = sortedFiles.map(file => {
            return new Promise((resolve) => {
                let formData = new FormData();
                formData.append('file', file);
                formData.append('$csrfParam', '$csrfToken');
                
                $.ajax({
                    url: '$uploadUrl',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        completed++;
                        let percent = Math.round((completed / total) * 100);
                        progressBar.style.width = percent + '%';
                        resolve(response);
                    },
                    error: function() {
                        completed++;
                        let percent = Math.round((completed / total) * 100);
                        progressBar.style.width = percent + '%';
                        resolve(null);
                    }
                });
            });
        });
        
        Promise.all(uploadPromises).then(results => {
            setTimeout(() => {
                progressBarContainer.style.display = 'none';
                progressBar.style.width = '0%';
                // Refresh grid using Pjax
                $.pjax.reload({container: '#file-center-grid'});
            }, 1000);
        });
    }
    
    // Copy link helper
    $(document).on('click', '.copy-link', function(e) {
        e.preventDefault();
        var url = $(this).data('url');
        var tempInput = document.createElement("input");
        tempInput.style = "position: absolute; left: -1000px; top: -1000px";
        var urlFrontend = '$urlFrontend';
        var baseUrl = urlFrontend ? urlFrontend : window.location.origin;
        tempInput.value = baseUrl + url;
        document.body.appendChild(tempInput);
        tempInput.select();
        document.execCommand("copy");
        document.body.removeChild(tempInput);
        
        var \$btn = $(this);
        var originalHtml = \$btn.html();
        \$btn.html('<i class="fa fa-check"></i> คัดลอกแล้ว');
        setTimeout(function() {
            \$btn.html(originalHtml);
        }, 2000);
    });
});
JS;
$this->registerJs($js);
?>
