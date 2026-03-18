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

/* Error Messages Below Dropzone */
.error-messages {
    color: #dc3545;
    font-size: 14px;
    margin-top: 10px;
    padding: 10px 0;
}

.error-message {
    margin-bottom: 5px;
    display: flex;
    align-items: center;
}

.error-message i {
    margin-right: 8px;
}

/* Custom Modal */
.custom-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
    z-index: 9998;
    display: flex;
    align-items: center;
    justify-content: center;
}

.custom-modal {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    max-width: 500px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
    animation: modalFadeIn 0.3s ease-out;
}

.custom-modal-header {
    padding: 20px 20px 10px;
    border-bottom: 1px solid #e9ecef;
}

.custom-modal-title {
    font-size: 18px;
    font-weight: 600;
    margin: 0;
    color: #333;
}

.custom-modal-body {
    padding: 20px;
}

.custom-modal-footer {
    padding: 10px 20px 20px;
    text-align: right;
    border-top: 1px solid #e9ecef;
}

.custom-modal-footer .btn {
    margin-left: 10px;
}

@keyframes modalFadeIn {
    from {
        opacity: 0;
        transform: scale(0.9);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}
CSS;
$this->registerCss($css);
?>
<div class="file-center-index box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title"><?= Html::encode($this->title) ?></h3>
    </div>
    <div class="box-body">
        
        <div id="error-messages" class="error-messages" style="display: none;"></div>
        
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
                
                // Helper function to truncate text
                $truncateText = function($text, $maxLength = 20) {
                    if (mb_strlen($text, 'UTF-8') <= $maxLength) {
                        return $text;
                    }
                    return mb_substr($text, 0, $maxLength, 'UTF-8') . '...';
                };
                
                $html = '<div class="file-item">';
                $html .= '<img src="' . $previewUrl . '" alt="' . Html::encode($model->alt_text) . '" title="' . Html::encode($model->file_name) . '">';
                $html .= '<div class="file-name" title="' . Html::encode($model->file_name) . '">' . Html::encode($truncateText($model->file_name, 25)) . '</div>';
                $labelText = $model->label ? 'ป้ายกำกับ: ' . $model->label : 'ไม่มีป้ายกำกับ';
                $html .= '<div class="file-label" title="' . Html::encode($model->label) . '" style="font-size: 11px; color: #007bff; margin-bottom: 5px;">' . Html::encode($truncateText($labelText, 30)) . '</div>';
                $html .= '<div class="file-size">' . $model->getFileSizeText() . '</div>';
                $html .= '<div class="file-item-actions">';
                $html .= Html::a('<i class="fa fa-trash"></i>', ['#'], [
                    'class' => 'btn btn-xs btn-danger delete-file',
                    'title' => 'ลบ',
                    'data-id' => $model->id,
                    'data-file-name' => $model->file_name,
                ]);
                $html .= ' ' . Html::button('<i class="fa fa-pencil"></i>', [
                    'class' => 'btn btn-xs btn-warning edit-label',
                    'title' => 'แก้ไขป้ายกำกับ',
                    'data-id' => $model->id,
                    'data-label' => $model->label,
                ]);
                $html .= ' ' . Html::button('<i class="fa fa-link"></i>', [
                    'class' => 'btn btn-xs btn-default copy-link',
                    'title' => 'คัดลอกลิงก์',
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
$updateLabelUrl = yii\helpers\Url::to(['file-center/update-label']);
$deleteUrl = yii\helpers\Url::to(['file-center/delete']);
$csrfParam = Yii::$app->request->csrfParam;
$csrfToken = Yii::$app->request->csrfToken;
$urlFrontend = isset(Yii::$app->params['urlFrontend']) ? Yii::$app->params['urlFrontend'] : "";
$uploadLimitsConfig = json_encode(isset(Yii::$app->params['fileCenterUploadLimits']) ? Yii::$app->params['fileCenterUploadLimits'] : []);

$js = <<<JS
// Simple Error Message System
function showError(message) {
    var container = document.getElementById('error-messages');
    if (!container) return;
    
    container.style.display = 'block';
    
    var errorDiv = document.createElement('div');
    errorDiv.className = 'error-message';
    errorDiv.innerHTML = '<i class="fa fa-exclamation-circle"></i>' + message;
    
    container.appendChild(errorDiv);
    
    // Auto-remove after 8 seconds
    setTimeout(function() {
        if (errorDiv.parentNode) {
            errorDiv.parentNode.removeChild(errorDiv);
        }
        if (container.children.length === 0) {
            container.style.display = 'none';
        }
    }, 8000);
    
    return errorDiv;
}

function showSuccess(message) {
    var container = document.getElementById('error-messages');
    if (!container) return;
    
    container.style.display = 'block';
    
    var successDiv = document.createElement('div');
    successDiv.className = 'error-message';
    successDiv.style.color = '#28a745';
    successDiv.innerHTML = '<i class="fa fa-check-circle"></i>' + message;
    
    container.appendChild(successDiv);
    
    // Auto-remove after 5 seconds
    setTimeout(function() {
        if (successDiv.parentNode) {
            successDiv.parentNode.removeChild(successDiv);
        }
        if (container.children.length === 0) {
            container.style.display = 'none';
        }
    }, 5000);
    
    return successDiv;
}

function showWarning(message) {
    var container = document.getElementById('error-messages');
    if (!container) return;
    
    container.style.display = 'block';
    
    var warningDiv = document.createElement('div');
    warningDiv.className = 'error-message';
    warningDiv.style.color = '#ffc107';
    warningDiv.innerHTML = '<i class="fa fa-exclamation-triangle"></i>' + message;
    
    container.appendChild(warningDiv);
    
    // Auto-remove after 6 seconds
    setTimeout(function() {
        if (warningDiv.parentNode) {
            warningDiv.parentNode.removeChild(warningDiv);
        }
        if (container.children.length === 0) {
            container.style.display = 'none';
        }
    }, 6000);
    
    return warningDiv;
}

function clearErrors() {
    var container = document.getElementById('error-messages');
    if (container) {
        container.innerHTML = '';
        container.style.display = 'none';
    }
}

// Custom Modal System
function confirmDialog(options) {
    return new Promise(function(resolve) {
        var overlay = document.createElement('div');
        overlay.className = 'custom-modal-overlay';
        
        var modal = document.createElement('div');
        modal.className = 'custom-modal';
        
        modal.innerHTML = 
            '<div class="custom-modal-header">' +
                '<h3 class="custom-modal-title">' + (options.title || 'ยืนยัน') + '</h3>' +
            '</div>' +
            '<div class="custom-modal-body">' +
                '<p>' + (options.message || '') + '</p>' +
            '</div>' +
            '<div class="custom-modal-footer">' +
                '<button class="btn btn-default" data-action="cancel">' + (options.cancelText || 'ยกเลิก') + '</button>' +
                '<button class="btn btn-danger" data-action="confirm">' + (options.confirmText || 'ยืนยัน') + '</button>' +
            '</div>';
        
        overlay.appendChild(modal);
        document.body.appendChild(overlay);
        
        // Button handlers
        modal.querySelector('[data-action="confirm"]').addEventListener('click', function() {
            document.body.removeChild(overlay);
            resolve(true);
        });
        
        modal.querySelector('[data-action="cancel"]').addEventListener('click', function() {
            document.body.removeChild(overlay);
            resolve(false);
        });
        
        // Close on overlay click
        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) {
                document.body.removeChild(overlay);
                resolve(false);
            }
        });
        
        // ESC key
        function escHandler(e) {
            if (e.key === 'Escape') {
                document.body.removeChild(overlay);
                document.removeEventListener('keydown', escHandler);
                resolve(false);
            }
        }
        document.addEventListener('keydown', escHandler);
    });
}

function promptDialog(options) {
    return new Promise(function(resolve) {
        var overlay = document.createElement('div');
        overlay.className = 'custom-modal-overlay';
        
        var modal = document.createElement('div');
        modal.className = 'custom-modal';
        
        modal.innerHTML = 
            '<div class="custom-modal-header">' +
                '<h3 class="custom-modal-title">' + (options.title || 'กรอกข้อมูล') + '</h3>' +
            '</div>' +
            '<div class="custom-modal-body">' +
                '<p>' + (options.message || '') + '</p>' +
                '<input type="text" class="form-control" id="prompt-input" value="' + (options.defaultValue || '') + '" placeholder="' + (options.placeholder || '') + '">' +
            '</div>' +
            '<div class="custom-modal-footer">' +
                '<button class="btn btn-default" data-action="cancel">' + (options.cancelText || 'ยกเลิก') + '</button>' +
                '<button class="btn btn-primary" data-action="confirm">' + (options.confirmText || 'ตกลง') + '</button>' +
            '</div>';
        
        overlay.appendChild(modal);
        document.body.appendChild(overlay);
        
        var input = modal.querySelector('#prompt-input');
        input.focus();
        input.select();
        
        // Button handlers
        modal.querySelector('[data-action="confirm"]').addEventListener('click', function() {
            document.body.removeChild(overlay);
            resolve(input.value);
        });
        
        modal.querySelector('[data-action="cancel"]').addEventListener('click', function() {
            document.body.removeChild(overlay);
            resolve(null);
        });
        
        // Enter key
        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                document.body.removeChild(overlay);
                resolve(input.value);
            }
        });
        
        // ESC key - only way to close besides buttons
        function escHandler(e) {
            if (e.key === 'Escape') {
                document.body.removeChild(overlay);
                document.removeEventListener('keydown', escHandler);
                resolve(null);
            }
        }
        document.addEventListener('keydown', escHandler);
        
        // Prevent closing on overlay click - remove this event listener
        // overlay.addEventListener('click', function(e) {
        //     if (e.target === overlay) {
        //         document.body.removeChild(overlay);
        //         resolve(null);
        //     }
        // });
    });
}

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
            showError('ประเภทไฟล์ "' + file.name + '" ไม่ได้รับอนุญาตให้ใช้ระบบเพิ่มไฟล์อัปโหลด');
            return false;
        }
        
        if (file.size > maxSize) {
            showError('ขนาดไฟล์ "' + file.name + '" ใหญ่เกินกำหนด (สูงสุด ' + (maxSize/(1024*1024)) + 'MB)');
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
                        
                        if (response.status === 'success') {
                            showSuccess('อัปโหลดไฟล์ "' + file.name + '" สำเร็จ');
                        } else {
                            showError('อัปโหลดไฟล์ "' + file.name + '" ไม่สำเร็จ: ' + (response.message || 'ข้อผิดพลาดที่ไม่ทราบสาเหตุ'));
                        }
                        resolve(response);
                    },
                    error: function(xhr, status, errorThrown) {
                        completed++;
                        let percent = Math.round((completed / total) * 100);
                        progressBar.style.width = percent + '%';
                        
                        let errorMessage = 'อัปโหลดไฟล์ "' + file.name + '" ไม่สำเร็จ';
                        if (xhr.status === 413) {
                            errorMessage += ': ขนาดไฟล์ใหญ่เกินกำหนด';
                        } else if (xhr.status === 422) {
                            errorMessage += ': ประเภทไฟล์ไม่ได้รับอนุญาต';
                        } else if (xhr.status >= 500) {
                            errorMessage += ': ข้อผิดพลาดที่เซิร์ฟเวอร์';
                        } else {
                            errorMessage += ': ' + (errorThrown || 'ข้อผิดพลาดที่ไม่ทราบสาเหตุ');
                        }
                        
                        showError(errorMessage);
                        resolve(null);
                    }
                });
            });
        });
        
        Promise.all(uploadPromises).then(results => {
            setTimeout(() => {
                progressBarContainer.style.display = 'none';
                progressBar.style.width = '0%';
                
                // Clear inline errors on successful completion
                var successCount = results.filter(r => r && r.status === 'success').length;
                var errorCount = results.length - successCount;
                
                if (successCount > 0 && errorCount === 0) {
                    clearErrors();
                    showSuccess('อัปโหลดไฟล์สำเร็จทั้งหมด ' + successCount + ' ไฟล์');
                } else if (successCount > 0 && errorCount > 0) {
                    showWarning('อัปโหลดสำเร็จ ' + successCount + ' ไฟล์, ไม่สำเร็จ ' + errorCount + ' ไฟล์');
                } else if (results.length > 0) {
                    showError('อัปโหลดไฟล์ไม่สำเร็จทั้งหมด');
                }
                
                // Refresh grid using Pjax
                $.pjax.reload({container: '#file-center-grid'});
            }, 1500);
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
        \$btn.html('<i class="fa fa-check"></i>');
        setTimeout(function() {
            \$btn.html(originalHtml);
        }, 2000);
    });

    // Edit label action
    $(document).on('click', '.edit-label', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        var currentLabel = $(this).data('label');
        
        promptDialog({
            title: 'แก้ไขป้ายกำกับ',
            message: 'กรุณากรอกชื่ออ้างอิงของรูปภาพ (ห้ามซ้ำกัน):',
            defaultValue: currentLabel || '',
            placeholder: 'ป้ายกำกับ...'
        }).then(function(newLabel) {
            if (newLabel !== null && newLabel !== currentLabel) {
                $.ajax({
                    url: '$updateLabelUrl' + '?id=' + id,
                    type: 'POST',
                    data: {
                        label: newLabel,
                        '$csrfParam': '$csrfToken'
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            showSuccess('แก้ไขป้ายกำกับสำเร็จ');
                            $.pjax.reload({container: '#file-center-grid'});
                        } else {
                            var errorMsg = response.message || 'เกิดข้อผิดพลาดในการแก้ไขป้ายกำกับ';
                            if (response.errors && response.errors.label) {
                                errorMsg += ' ' + response.errors.label.join(' ');
                            }
                            showError(errorMsg);
                        }
                    },
                    error: function() {
                        showError('เกิดข้อผิดพลาดในการเชื่อมต่อ');
                    }
                });
            }
        });
    });

    // Delete file action
    $(document).on('click', '.delete-file', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        var fileName = $(this).data('file-name');
        
        confirmDialog({
            title: 'ยืนยันการลบไฟล์',
            message: 'คุณแน่ใจหรือไม่ที่จะลบไฟล์ "' + fileName + '" นี้?',
            confirmText: 'ลบ',
            cancelText: 'ยกเลิก'
        }).then(function(confirmed) {
            if (confirmed) {
                $.ajax({
                    url: '$deleteUrl?id=' + id,
                    type: 'POST',
                    data: {
                        '$csrfParam': '$csrfToken'
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            showSuccess('ลบไฟล์ "' + fileName + '" สำเร็จ');
                            $.pjax.reload({container: '#file-center-grid'});
                        } else {
                            showError('ลบไฟล์ "' + fileName + '" ไม่สำเร็จ: ' + (response.message || 'ข้อผิดพลาดที่ไม่ทราบสาเหตุ'));
                        }
                    },
                    error: function(xhr, textStatus, errorThrown) {
                        if (xhr.status === 404) {
                            showError('ไม่พบไฟล์ที่ต้องการลบ');
                        } else if (xhr.status === 403) {
                            showError('คุณไม่มีสิทธิ์ลบไฟล์นี้');
                        } else {
                            showError('เกิดข้อผิดพลาดในการลบไฟล์: ' + (errorThrown || 'ข้อผิดพลาดที่ไม่ทราบสาเหตุ'));
                        }
                    }
                });
            }
        });
    });
});
JS;
$this->registerJs($js);
?>
