/**
 * AsyncExportModal - Reusable export modal component
 * Handles async export functionality for all content types
 */
class AsyncExportModal {
    constructor(options) {
        this.options = Object.assign({
            contentType: 'content',
            modalTitle: 'Export ข้อมูล',
            startExportUrl: '',
            exportStatusUrl: '',
            searchParams: {},
            pollInterval: 2000
        }, options);

        this.modalId = this.options.contentType + 'ExportModal';
        this.pollTimer = null;
        
        this.init();
    }

    init() {
        this.bindEvents();
    }

    bindEvents() {
        const self = this;
        const prefix = this.options.contentType;

        // Open modal button
        $('#open' + this.capitalize(prefix) + 'ExportModal').on('click', function() {
            self.openModal();
        });

        // Submit export button
        $('#' + prefix + 'ExportSubmitBtn').on('click', function() {
            self.startExport();
        });

        // Success close button
        $('#' + prefix + 'ExportSuccessCloseBtn').on('click', function() {
            self.resetModal();
        });

        // Modal close event
        $('#' + this.modalId).on('hidden.bs.modal', function() {
            self.resetModal();
        });
    }

    capitalize(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

    openModal() {
        $('#' + this.modalId).modal('show');
    }

    closeModal() {
        $('#' + this.modalId).modal('hide');
    }

    setStatus(message, type) {
        const statusBox = $('#' + this.options.contentType + 'ExportStatusBox');
        statusBox.removeClass('alert-warning alert-danger alert-success alert-info');
        statusBox.addClass(type || 'alert-info');
        statusBox.text(message);
        statusBox.show();
    }

    toggleLoading(isLoading) {
        const submitBtn = $('#' + this.options.contentType + 'ExportSubmitBtn');
        const cancelBtn = $('#' + this.options.contentType + 'ExportCancelBtn');
        
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

    showSuccessState() {
        $('#' + this.options.contentType + 'ExportFormState').hide();
        $('#' + this.options.contentType + 'ExportSuccessState').show();
        $('#' + this.options.contentType + 'ExportInitialFooter').hide();
        $('#' + this.options.contentType + 'ExportSuccessFooter').show();
    }

    updateProgressText(text) {
        $('#' + this.options.contentType + 'ExportProgressText').text(text);
    }

    startExport() {
        const self = this;
        const dateFrom = $('#' + this.options.contentType + 'ExportDateFrom').val();
        const dateTo = $('#' + this.options.contentType + 'ExportDateTo').val();

        console.log('dateFrom', dateFrom, 'dateTo', dateTo);

        // ALL content types require date validation
        if (!dateFrom || !dateTo) {
            console.log("ERROR", 'date')
            this.setStatus('กรุณาเลือกช่วงวันที่ให้ครบถ้วน', 'alert-danger');
            this.toggleLoading(false);
            return;
        }

        if (dateFrom > dateTo) {
            this.setStatus('วันที่เริ่มต้นต้องไม่มากกว่าวันที่สิ้นสุด', 'alert-danger');
            this.toggleLoading(false);
            return;
        }

        console.log("ALL GOOD")

        this.toggleLoading(true);
        this.setStatus('กำลังเริ่มต้นการ Export...', 'alert-info');

        const postData = Object.assign({}, this.options.searchParams, {
            content_type: 'content_' + this.options.contentType,
            date_from: dateFrom,
            date_to: dateTo
        });

        $.ajax({
            url: this.options.startExportUrl,
            method: 'POST',
            data: postData,
            dataType: 'json',
            success: function(response) {
                console.log('Response:', response);
                if (response.jobId) {
                    self.showSuccessState();
                    self.updateProgressText('กำลังเริ่มต้นการ Export...');
                    self.pollExportStatus(response.jobId);
                } else {
                    self.setStatus('เกิดข้อผิดพลาด: ' + (response.message || 'ไม่สามารถเริ่มต้นการ Export ได้'), 'alert-danger');
                    self.toggleLoading(false);
                }
            },
            error: function(xhr, status, error) {
                self.setStatus('เกิดข้อผิดพลาดในการเชื่อมต่อ: ' + error, 'alert-danger');
                self.toggleLoading(false);
            }
        });
    }

    pollExportStatus(jobId) {
        const self = this;
        
        // Clear any existing timer
        if (this.pollTimer) {
            clearInterval(this.pollTimer);
        }

        this.pollTimer = setInterval(function() {
            $.ajax({
                url: self.options.exportStatusUrl,
                method: 'GET',
                data: { jobId: jobId },
                dataType: 'json',
                success: function(response) {
                    console.log('Status response:', response);
                    if (response.status === 'success' && response.job) {
                        if (response.job.state === 'completed') {
                            self.updateProgressText('Export เสร็จสมบูรณ์! ไฟล์พร้อมดาวน์โหลด');
                            clearInterval(self.pollTimer);
                            self.pollTimer = null;
                            // Start download
                            if (response.job.downloadReady && response.job.downloadUrl) {
                                window.location.href = response.job.downloadUrl;
                                setTimeout(function () {
                                    $('#' + self.options.contentType + 'ExportModal').modal('hide');
                                }, 800);
                            }
                        } else if (response.job.state === 'processing') {
                            const progress = response.job.progress || 0;
                            const message = response.job.progressMessage || 'กำลังดำเนินการ Export...';
                            self.updateProgressText(message + ' ' + progress + '%');
                        } else if (response.job.state === 'failed') {
                            const errorMsg = response.job.errorMessage || 'Export ล้มเหลว';
                            self.updateProgressText(errorMsg);
                            clearInterval(self.pollTimer);
                            self.pollTimer = null;
                        }
                    } else {
                        self.updateProgressText('เกิดข้อผิดพลาดในการตรวจสอบสถานะ');
                        clearInterval(self.pollTimer);
                        self.pollTimer = null;
                    }
                },
                error: function() {
                    self.updateProgressText('เกิดข้อผิดพลาดในการตรวจสอบสถานะ');
                    clearInterval(self.pollTimer);
                    self.pollTimer = null;
                }
            });
        }, this.options.pollInterval);
    }

    resetModal() {
        // Clear poll timer
        if (this.pollTimer) {
            clearInterval(this.pollTimer);
            this.pollTimer = null;
        }

        // Reset form
        $('#' + this.options.contentType + 'ExportDateFrom').val('');
        $('#' + this.options.contentType + 'ExportDateTo').val('');

        // Reset states
        $('#' + this.options.contentType + 'ExportFormState').show();
        $('#' + this.options.contentType + 'ExportSuccessState').hide();
        $('#' + this.options.contentType + 'ExportInitialFooter').show();
        $('#' + this.options.contentType + 'ExportSuccessFooter').hide();

        // Reset status
        $('#' + this.options.contentType + 'ExportStatusBox').hide();
        this.toggleLoading(false);
    }
}

// Make it available globally
window.AsyncExportModal = AsyncExportModal;
