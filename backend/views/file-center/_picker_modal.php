<?php
use yii\helpers\Url;

$listUrl = Url::to(['/file-center/list-api']);
$uploadUrl = Url::to(['/file-center/upload']);
?>

<div class="modal fade" id="fileCenterModal" tabindex="-1" role="dialog" aria-labelledby="fileCenterModalLabel" data-backdrop="static">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="fileCenterModalLabel"><i class="fa fa-folder-open"></i> File Center (เลือกไฟล์)</h4>
      </div>
      <div class="modal-body p-0">
          
          <!-- Nav tabs -->
          <ul class="nav nav-tabs" role="tablist" style="margin-bottom: 15px;">
            <li role="presentation" class="active"><a href="#fc-browse-tab" aria-controls="fc-browse-tab" role="tab" data-toggle="tab"><i class="fa fa-search"></i> Browse</a></li>
            <li role="presentation"><a href="#fc-upload-tab" aria-controls="fc-upload-tab" role="tab" data-toggle="tab"><i class="fa fa-upload"></i> Upload</a></li>
          </ul>

          <!-- Tab panes -->
          <div class="tab-content" style="padding: 0 15px;">
            <!-- BROWSE TAB -->
            <div role="tabpanel" class="tab-pane active" id="fc-browse-tab">
                <div class="row" style="margin-bottom: 15px;">
                    <div class="col-md-8">
                        <input type="text" id="fc-search-input" class="form-control" placeholder="ค้นหาชื่อไฟล์...">
                    </div>
                    <div class="col-md-4">
                        <button type="button" id="fc-search-btn" class="btn btn-default"><i class="fa fa-search"></i> ค้นหา</button>
                    </div>
                </div>
                
                <div id="fc-gallery" class="row" style="height: 400px; overflow-y: auto;">
                    <!-- Images will be loaded here via AJAX -->
                </div>
                <div class="text-center" style="margin-top: 15px;">
                    <button type="button" id="fc-load-more" class="btn btn-primary" style="display: none;">โหลดเพิ่มเติม</button>
                </div>
            </div>

            <!-- UPLOAD TAB -->
            <div role="tabpanel" class="tab-pane" id="fc-upload-tab">
                <div style="height: 400px; display: flex; align-items: center; justify-content: center; border: 2px dashed #ccc; border-radius: 8px; background: #fafafa; padding: 20px;">
                    <div class="text-center" style="width: 100%;">
                        <i class="fa fa-cloud-upload fa-4x text-muted" style="margin-bottom: 15px;"></i>
                        <h4>ลากไฟล์มาวางที่นี่ หรือคลิกเพื่อค้นหา</h4>
                        <p class="text-muted" id="fc-upload-hint">ขนาดไฟล์สูงสุด</p>
                        <input type="file" id="fc-upload-input" style="display:none;" multiple>
                        <button type="button" class="btn btn-success mt-2" onclick="$('#fc-upload-input').click()">เลือกไฟล์</button>
                        <div id="fc-upload-progress" class="progress" style="margin-top: 20px; display: none;">
                            <div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%">
                                0%
                            </div>
                        </div>
                        <div id="fc-upload-msg" style="margin-top: 10px;"></div>
                    </div>
                </div>
            </div>
          </div>
          
      </div>
      <div class="modal-footer">
        <div class="pull-left" id="fc-selection-info" style="display:none; line-height:34px;">
            เลือกแล้ว <span id="fc-selection-count">0</span> ไฟล์ <span id="fc-selection-limit-text"></span>
        </div>
        <button type="button" class="btn btn-default" data-dismiss="modal">ปิด</button>
        <button type="button" class="btn btn-primary" id="fc-confirm-selection" style="display:none;">ยืนยันการเลือก</button>
      </div>
    </div>
  </div>
</div>

<style>
.fc-item {
    cursor: pointer;
    border: 2px solid transparent;
    border-radius: 4px;
    padding: 5px;
    margin-bottom: 15px;
    transition: all 0.2s;
    position: relative;
    background: #fff;
}
.fc-item:hover {
    border-color: #3c8dbc;
    background: #f4f4f4;
}
.fc-item.selected {
    border-color: #00a65a;
    background: #e8f5e9;
}
.fc-item.selected::after {
    content: '\f00c';
    font-family: 'FontAwesome';
    position: absolute;
    top: 5px;
    right: 5px;
    background: #00a65a;
    color: white;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    text-align: center;
    line-height: 24px;
    font-size: 12px;
}
.fc-item img {
    width: 100%;
    height: 100px;
    object-fit: contain;
}
.fc-item-name {
    font-size: 11px;
    text-align: center;
    word-break: break-all;
    overflow: hidden;
    height: 30px;
    margin-top: 5px;
}
#fc-upload-tab .dragover {
    background-color: #e3f2fd;
    border-color: #2196f3;
}
</style>

<script>
window.currentFilePickerConfig = null;
window.selectedFiles = [];

// API to open the picker
window.openFileCenterPicker = function(config) {
    window.currentFilePickerConfig = config;
    window.selectedFiles = []; // Reset selection
    
    // Setup UI based on config
    var hintText = 'อัพโหลดไฟล์ (';
    if (config.extensions && config.extensions.length > 0) {
        hintText += config.extensions.join(', ') + ') ';
    }
    if (config.maxSize > 0) {
        hintText += 'ขนาดสูงสุด ' + (config.maxSize / 1024).toFixed(2) + ' MB';
    }
    $('#fc-upload-hint').text(hintText);
    
    if (config.multiple) {
        if (config.maxImages > 0) {
            $('#fc-selection-limit-text').text('(สูงสุด ' + config.maxImages + ' ไฟล์)');
        } else {
            $('#fc-selection-limit-text').text('');
        }
    } else {
        $('#fc-selection-limit-text').text('');
    }
    // ensure attr multiple matches config 
    if (config.multiple) {
        $('#fc-upload-input').attr('multiple', 'multiple');
    } else {
         $('#fc-upload-input').removeAttr('multiple');
    }
    
    window.updateSelectionUI();
    $('#fileCenterModal').modal('show');
    
    // Try to click browse tab and load
    $('.nav-tabs a[href="#fc-browse-tab"]').tab('show');
    if (typeof window.loadFiles === "function") {
        $('#fc-search-input').val('');
        window.loadFiles(1);
    }
};

window.clearFileCenterPicker = function(inputId, config) {
    $('#' + inputId).val('');
    var previewId = inputId + '-preview';
    if ($('#' + previewId).length) {
        $('#' + previewId).html('');
    }
};

document.addEventListener('DOMContentLoaded', function() {
    var currentPage = 1;
    var currentQuery = '';
    var gallery = document.getElementById('fc-gallery');
    var loadMoreBtn = document.getElementById('fc-load-more');
    
    $('#fc-search-btn').on('click', function() {
        currentQuery = $('#fc-search-input').val();
        gallery.innerHTML = '';
        window.loadFiles(1);
    });
    
    $('#fc-search-input').on('keypress', function(e) {
        if(e.which == 13) {
            $('#fc-search-btn').click();
        }
    });

    $('#fc-load-more').on('click', function() {
        window.loadFiles(currentPage + 1);
    });
    
    // Delegate click event for selecting files
    $(document).on('click', '.fc-item', function() {
        var fileData = {
            id: $(this).data('id'),
            path: $(this).data('path'),
            name: $(this).find('.fc-item-name').text(),
            url: $(this).find('img').attr('src')
        };
        
        var cfg = window.currentFilePickerConfig;
        if (!cfg) return;
        
        var isSelected = $(this).hasClass('selected');
        
        if (cfg.multiple) {
            if (isSelected) {
                $(this).removeClass('selected');
                window.selectedFiles = window.selectedFiles.filter(function(f) { return f.id !== fileData.id; });
            } else {
                if (cfg.maxImages > 0 && window.selectedFiles.length >= cfg.maxImages) {
                    alert('คุณสามารถเลือกได้สูงสุดแค่ ' + cfg.maxImages + ' ไฟล์');
                    return;
                }
                $(this).addClass('selected');
                window.selectedFiles.push(fileData);
            }
            window.updateSelectionUI();
        } else {
            // Single select mode
            window.selectedFiles = [fileData];
            $('.fc-item').removeClass('selected');
            $(this).addClass('selected');
            
            // Auto confirm if single
            window.confirmSelection();
        }
    });
    
    $('#fc-confirm-selection').on('click', function() {
        window.confirmSelection();
    });

    window.loadFiles = function(page) {
        var cfg = window.currentFilePickerConfig || {};
        var exts = cfg.extensions && cfg.extensions.length ? cfg.extensions.join(',') : '';
        
        $.ajax({
            url: '<?= $listUrl ?>',
            type: 'GET',
            data: { page: page, q: currentQuery, extensions: exts },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    currentPage = response.pagination.currentPage;
                    
                    var html = '';
                    response.data.forEach(function(file) {
                        var previewUrl = file.is_image ? file.file_url : '/images/default.png';
                        // check if already selected
                        var isSelected = window.selectedFiles.some(function(f) { return f.id === file.id; });
                        var selectedClass = isSelected ? 'selected' : '';
                        
                        html += '<div class="col-md-3 col-xs-6">';
                        html += '<div class="fc-item ' + selectedClass + '" data-id="' + file.id + '" data-path="' + file.file_path + '">';
                        html += '<img src="' + previewUrl + '" alt="file">';
                        html += '<div class="fc-item-name" title="' + file.file_name + '">' + file.file_name + '</div>';
                        html += '</div></div>';
                    });
                    
                    if (page === 1) {
                        gallery.innerHTML = html;
                    } else {
                        gallery.innerHTML += html;
                    }
                    
                    if (currentPage < response.pagination.pageCount) {
                        loadMoreBtn.style.display = 'inline-block';
                    } else {
                        loadMoreBtn.style.display = 'none';
                    }
                }
            }
        });
    };
    
    window.updateSelectionUI = function() {
        var count = window.selectedFiles.length;
        if (window.currentFilePickerConfig && window.currentFilePickerConfig.multiple) {
            $('#fc-selection-info').show();
            $('#fc-selection-count').text(count);
            if (count > 0) {
                $('#fc-confirm-selection').show();
            } else {
                $('#fc-confirm-selection').hide();
            }
        } else {
            $('#fc-selection-info').hide();
            $('#fc-confirm-selection').hide();
        }
    };
    
    window.confirmSelection = function() {
        if (window.selectedFiles.length === 0) return;
        var cfg = window.currentFilePickerConfig;
        if (!cfg) return;
        
        var inputId = cfg.inputId;
        
        if (cfg.multiple) {
            // we will join paths by comma
            var paths = window.selectedFiles.map(function(f) { return f.path; });
            $('#' + inputId).val(paths.join(','));
            var previewId = inputId + '-preview';
            if ($('#' + previewId).length) {
                var imgs = '';
                window.selectedFiles.forEach(function(f) {
                    imgs += '<img src="' + f.url + '" class="img-responsive img-thumbnail" style="max-height: 150px; display:inline-block; margin-right:5px; margin-bottom:5px;" />';
                });
                $('#' + previewId).html(imgs);
            }
        } else {
            var fileData = window.selectedFiles[0];
            $('#' + inputId).val(fileData.path);
            var previewId = inputId + '-preview';
            if ($('#' + previewId).length) {
                $('#' + previewId).html('<img src="' + fileData.url + '" class="img-responsive img-thumbnail" style="max-height: 200px;" />');
            }
        }
        
        $('#fileCenterModal').modal('hide');
    };
    
    // --- UPLOAD PROCESS ---
    var uploadContainer = $('#fc-upload-tab > div');
    
    uploadContainer.on('dragover', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).addClass('dragover');
    });
    
    uploadContainer.on('dragleave', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).removeClass('dragover');
    });
    
    uploadContainer.on('drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $(this).removeClass('dragover');
        
        var files = e.originalEvent.dataTransfer.files;
        if (files.length > 0) {
            handleUploadFiles(files);
        }
    });
    
    $('#fc-upload-input').on('change', function() {
        if (this.files.length > 0) {
            handleUploadFiles(this.files);
        }
    });
    
    function handleUploadFiles(files) {
        var cfg = window.currentFilePickerConfig || {};
        var maxFilesAllowed = cfg.multiple ? (cfg.maxImages > 0 ? cfg.maxImages : files.length) : 1;
        
        if (!cfg.multiple && files.length > 1) {
            alert('คุณสามารถเลือกอัพโหลดได้เพียง 1 ไฟล์เท่านั้น');
            return;
        }
        
        if (cfg.multiple && cfg.maxImages > 0 && (window.selectedFiles.length + files.length) > cfg.maxImages) {
             alert('คุณสามารถเลือกได้สูงสุดแค่ ' + cfg.maxImages + ' ไฟล์');
             return;
        }
        
        // Setup Progress
        $('#fc-upload-progress').show();
        var progressBar = $('#fc-upload-progress .progress-bar');
        progressBar.css('width', '0%').attr('aria-valuenow', 0).text('0%');
        $('#fc-upload-msg').html('<span class="text-info">กำลังอัพโหลด...</span>');
        
        var uploadedCount = 0;
        var totalFiles = Math.min(files.length, maxFilesAllowed);
        var hasError = false;
        
        function uploadSingleFile(index) {
            if (index >= totalFiles) {
                setTimeout(function() {
                    $('#fc-upload-progress').hide();
                    $('#fc-upload-input').val('');
                    if (!hasError) {
                        $('#fc-upload-msg').html('<span class="text-success">อัพโหลดสำเร็จ!</span>');
                        
                        if (!cfg.multiple) {
                            window.confirmSelection();
                        } else {
                            window.updateSelectionUI();
                            $('.nav-tabs a[href="#fc-browse-tab"]').tab('show');
                            window.loadFiles(1);
                        }
                    }
                }, 1000);
                return;
            }
            
            var file = files[index];
            var formData = new FormData();
            formData.append('file', file);
            
            $.ajax({
                url: '<?= $uploadUrl ?>',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                xhr: function() {
                    var xhr = new window.XMLHttpRequest();
                    xhr.upload.addEventListener("progress", function(evt) {
                        if (evt.lengthComputable) {
                            var basePercent = (index / totalFiles) * 100;
                            var filePercent = (evt.loaded / evt.total) * (100 / totalFiles);
                            var totalPercent = Math.round(basePercent + filePercent);
                            progressBar.css('width', totalPercent + '%').attr('aria-valuenow', totalPercent).text(totalPercent + '%');
                        }
                    }, false);
                    return xhr;
                },
                success: function(res) {
                    if (res.status === 'success') {
                        var fileData = {
                            id: res.file.id,
                            path: res.file.path,
                            name: res.file.name,
                            url: urlWebBiog + res.file.path // preview url
                        };
                        
                        if (cfg.multiple) {
                            window.selectedFiles.push(fileData);
                        } else {
                            window.selectedFiles = [fileData];
                        }
                    } else {
                        hasError = true;
                        $('#fc-upload-msg').append('<div class="text-danger">' + file.name + ': ' + res.message + '</div>');
                    }
                },
                error: function() {
                    hasError = true;
                    $('#fc-upload-msg').append('<div class="text-danger">' + file.name + ': เกิดข้อผิดพลาดในการอัพโหลด</div>');
                },
                complete: function() {
                    uploadSingleFile(index + 1);
                }
            });
        }
        
        uploadSingleFile(0);
    }
});
</script>
