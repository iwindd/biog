<?php
use yii\helpers\Url;

$listUrl = Url::to(['/file-center/list-api']);
?>

<div class="modal fade" id="fileCenterModal" tabindex="-1" role="dialog" aria-labelledby="fileCenterModalLabel">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="fileCenterModalLabel"><i class="fa fa-folder-open"></i> File Center (เลือกไฟล์)</h4>
      </div>
      <div class="modal-body">
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
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">ปิด</button>
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
}
.fc-item:hover {
    border-color: #3c8dbc;
    background: #f4f4f4;
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
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var currentPage = 1;
    var currentQuery = '';
    var gallery = document.getElementById('fc-gallery');
    var loadMoreBtn = document.getElementById('fc-load-more');
    var isModalLoaded = false;
    
    $('#fileCenterModal').on('show.bs.modal', function () {
        if (!isModalLoaded) {
            loadFiles(1);
            isModalLoaded = true;
        }
    });
    
    $('#fc-search-btn').on('click', function() {
        currentQuery = $('#fc-search-input').val();
        gallery.innerHTML = '';
        loadFiles(1);
    });
    
    $('#fc-search-input').on('keypress', function(e) {
        if(e.which == 13) {
            $('#fc-search-btn').click();
        }
    });

    $('#fc-load-more').on('click', function() {
        loadFiles(currentPage + 1);
    });
    
    // Delegate click event for selecting files
    $(document).on('click', '.fc-item', function() {
        var filePath = $(this).data('path');
        var inputId = window.currentFilePickerInput;
        
        if (inputId) {
            $('#' + inputId).val(filePath);
            // Optional: if it is bound to an image preview
            var previewId = inputId + '-preview';
            if ($('#' + previewId).length) {
                $('#' + previewId).attr('src', filePath);
            }
        }
        
        $('#fileCenterModal').modal('hide');
    });

    function loadFiles(page) {
        $.ajax({
            url: '<?= $listUrl ?>',
            type: 'GET',
            data: { page: page, q: currentQuery },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    currentPage = response.pagination.currentPage;
                    
                    var html = '';
                    response.data.forEach(function(file) {
                        var previewUrl = file.is_image ? file.file_url : '/images/default.png';
                        html += '<div class="col-md-3 col-xs-6">';
                        html += '<div class="fc-item" data-id="' + file.id + '" data-path="' + file.file_path + '">';
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
    }
});
</script>
