/**
 * AsyncExportModal - Client-side export with batched data fetch and XLSX/ZIP generation
 *
 * Flow:
 * 1. User selects date range and clicks "เริ่ม Export"
 * 2. Client fetches data page by page (3 concurrent requests) from /export/fetch-data
 * 3. Accumulates all rows in memory
 * 4. Generates XLSX using SheetJS
 * 5. Creates ZIP using JSZip
 * 6. Downloads via FileSaver
 *
 * Dependencies: jQuery, XLSX (SheetJS), JSZip, FileSaver
 */
class AsyncExportModal {
    constructor(options) {
        this.options = Object.assign({
            contentType: 'content',
            modalTitle: 'Export ข้อมูล',
            fetchDataUrl: '',
            searchParams: {},
            pageSize: 3000,
            maxConcurrentRequests: 3,
            largeDatasetThreshold: 50000
        }, options);

        this.modalId = this.options.contentType + 'ExportModal';
        this.allRows = [];
        this.allHeaders = [];
        this.baseFileName = '';
        this.totalRows = 0;
        this.totalPages = 0;
        this.fetchedPages = 0;
        this.isCancelled = false;
        this.activeRequests = [];
        this._retryCount = {};

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

        // Modal close event - cancel any pending requests
        $('#' + this.modalId).on('hidden.bs.modal', function() {
            self.cancelExport();
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

    showProgressState() {
        $('#' + this.options.contentType + 'ExportFormState').hide();
        $('#' + this.options.contentType + 'ExportSuccessState').show();
        $('#' + this.options.contentType + 'ExportInitialFooter').hide();
        $('#' + this.options.contentType + 'ExportSuccessFooter').hide();

        // Reset success state UI back to progress state
        const $state = $('#' + this.options.contentType + 'ExportSuccessState');
        $state.find('.progress').show();
        $state.find('.progress-bar').css('width', '0%');
        $state.find('#' + this.options.contentType + 'ExportProgressPercent').text('0%');
        $state.find('#' + this.options.contentType + 'ExportProgressText').closest('div').show();
        $state.find('#' + this.options.contentType + 'ExportProgressText').text('กำลังเริ่มต้นการ Export...');

        // Reset icon back to spinner
        $state.find('.fa-check-circle')
            .removeClass('fa-check-circle')
            .addClass('fa-spinner fa-spin');

        // Reset h4 text back to "กำลังดำเนินการ..."
        $state.find('h4').first().text('กำลังดำเนินการ...');
    }

    showSuccessState() {
        $('#' + this.options.contentType + 'ExportFormState').hide();
        $('#' + this.options.contentType + 'ExportSuccessState').show();
        $('#' + this.options.contentType + 'ExportInitialFooter').hide();
        $('#' + this.options.contentType + 'ExportSuccessFooter').show();

        // Hide progress bar and update to success state
        $('#' + this.options.contentType + 'ExportProgressBar').closest('.progress').hide();
        $('#' + this.options.contentType + 'ExportProgressText').closest('div').hide();

        // Change icon from spinner to check
        $('#' + this.options.contentType + 'ExportSuccessState').find('.fa-spinner')
            .removeClass('fa-spinner fa-spin')
            .addClass('fa-check-circle');

        // Update text from "กำลังดำเนินการ..." to "เสร็จสิ้นแล้ว"
        $('#' + this.options.contentType + 'ExportSuccessState').find('h4').text('เสร็จสิ้นแล้ว');
    }

    updateProgressText(text) {
        $('#' + this.options.contentType + 'ExportProgressText').text(text);
    }

    updateProgressBar(percent) {
        const $bar = $('#' + this.options.contentType + 'ExportProgressBar');
        const $percent = $('#' + this.options.contentType + 'ExportProgressPercent');
        $bar.css('width', percent + '%');
        $percent.text(percent + '%');
    }

    cancelExport() {
        this.isCancelled = true;
        // Abort all active AJAX requests
        this.activeRequests.forEach(function(xhr) {
            try { xhr.abort(); } catch(e) {}
        });
        this.activeRequests = [];
    }

    resetModal() {
        this.cancelExport();

        // Reset data
        this.allRows = [];
        this.allHeaders = [];
        this.baseFileName = '';
        this.totalRows = 0;
        this.totalPages = 0;
        this.fetchedPages = 0;
        this.isCancelled = false;
        this._retryCount = {};

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

    startExport() {
        const self = this;
        const dateFrom = $('#' + this.options.contentType + 'ExportDateFrom').val();
        const dateTo = $('#' + this.options.contentType + 'ExportDateTo').val();

        // Validate dates
        if (!dateFrom || !dateTo) {
            this.setStatus('กรุณาเลือกช่วงวันที่ให้ครบถ้วน', 'alert-danger');
            return;
        }

        if (dateFrom > dateTo) {
            this.setStatus('วันที่เริ่มต้นต้องไม่มากกว่าวันที่สิ้นสุด', 'alert-danger');
            return;
        }

        // Reset state
        this.allRows = [];
        this.allHeaders = [];
        this.baseFileName = '';
        this.isCancelled = false;
        this.fetchedPages = 0;
        this._retryCount = {};

        // Check dependencies
        if (typeof XLSX === 'undefined') {
            console.error(`not found XLSX library. Please refresh the page and try again.`);
            this.setStatus('เกิดข้อผิดพลาดกรุณาลองใหม่อีกครั้งภายหลัง!', 'alert-danger');
            return;
        }
        
        if (typeof JSZip === 'undefined') {
            console.error(`not found JSZip library. Please refresh the page and try again.`);
            this.setStatus('เกิดข้อผิดพลาดกรุณาลองใหม่อีกครั้งภายหลัง!', 'alert-danger');
            return;
        }

        this.toggleLoading(true);
        this.showProgressState();
        this.updateProgressText('กำลังเตรียมดึงข้อมูล...');

        // Fetch first page to get total count and headers
        this.fetchPage(1, dateFrom, dateTo);
    }

    fetchPage(page, dateFrom, dateTo) {
        const self = this;

        if (this.isCancelled) return;

        const params = Object.assign({}, this.options.searchParams, {
            content_type: 'content_' + this.options.contentType,
            date_from: dateFrom,
            date_to: dateTo,
            page: page,
            per_page: this.options.pageSize
        });

        const queryString = jQuery.param(params);

        const xhr = jQuery.ajax({
            url: this.options.fetchDataUrl + '?' + queryString,
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                // Remove from active requests
                self.activeRequests = self.activeRequests.filter(function(r) { return r !== xhr; });

                if (self.isCancelled) return;

                if (response.status === 'error') {
                    self.setStatus('เกิดข้อผิดพลาด: ' + (response.message || 'ไม่สามารถดึงข้อมูลได้'), 'alert-danger');
                    self.toggleLoading(false);
                    $('#' + self.options.contentType + 'ExportFormState').show();
                    $('#' + self.options.contentType + 'ExportSuccessState').hide();
                    $('#' + self.options.contentType + 'ExportInitialFooter').show();
                    return;
                }

                // On first page, setup metadata
                if (page === 1) {
                    self.totalRows = response.total;
                    self.totalPages = response.total_pages;
                    self.allHeaders = response.headers || [];
                    self.baseFileName = response.base_file_name || 'Export';

                    // Show large dataset warning
                    if (response.large_dataset_warning) {
                        if (!confirm('ข้อมูลมีจำนวนมาก (' + self.totalRows.toLocaleString() + ' รายการ) อาจใช้เวลานานในการประมวลผล ต้องการดำเนินการต่อหรือไม่?')) {
                            self.isCancelled = true;
                            self.resetModal();
                            return;
                        }
                    }

                    // Check if no data
                    if (self.totalRows === 0) {
                        self.setStatus('ไม่พบข้อมูลตามเงื่อนไขที่เลือก', 'alert-warning');
                        self.toggleLoading(false);
                        $('#' + self.options.contentType + 'ExportFormState').show();
                        $('#' + self.options.contentType + 'ExportSuccessState').hide();
                        $('#' + self.options.contentType + 'ExportInitialFooter').show();
                        return;
                    }
                }

                // Append rows
                if (response.rows && response.rows.length > 0) {
                    self.allRows = self.allRows.concat(response.rows);
                }
                self.fetchedPages++;

                // Update progress (0-70% for data fetching)
                const progressPercent = self.totalPages > 0
                    ? Math.round((self.fetchedPages / self.totalPages) * 70)
                    : 0;
                self.updateProgressBar(progressPercent);
                self.updateProgressText(
                    'กำลังโหลดข้อมูล... หน้า ' + self.fetchedPages + '/' + self.totalPages +
                    ' (' + self.allRows.length.toLocaleString() + '/' + self.totalRows.toLocaleString() + ' รายการ) ' + progressPercent + '%'
                );

                // If first page, start parallel fetching for remaining pages
                if (page === 1 && self.totalPages > 1) {
                    self.fetchRemainingPages(dateFrom, dateTo);
                } else if (self.fetchedPages >= self.totalPages) {
                    // All pages fetched, generate file
                    self.generateAndDownload();
                }
            },
            error: function(xhr, status, error) {
                self.activeRequests = self.activeRequests.filter(function(r) { return r !== xhr; });

                if (self.isCancelled) return;
                if (status === 'abort') return; // Cancelled by user

                // Retry logic
                self._retryCount = self._retryCount || {};
                self._retryCount[page] = (self._retryCount[page] || 0) + 1;

                if (self._retryCount[page] <= 3) {
                    console.warn('Retrying page ' + page + ' (attempt ' + self._retryCount[page] + ')');
                    setTimeout(function() {
                        self.fetchPage(page, dateFrom, dateTo);
                    }, 1000 * self._retryCount[page]);
                } else {
                    self.setStatus('เกิดข้อผิดพลาดในการดึงข้อมูลหน้า ' + page + ': ' + error, 'alert-danger');
                    self.toggleLoading(false);
                }
            }
        });

        this.activeRequests.push(xhr);
    }

    fetchRemainingPages(dateFrom, dateTo) {
        const self = this;
        const maxConcurrent = this.options.maxConcurrentRequests;
        const totalPages = this.totalPages;
        let nextPage = 2; // Page 1 already fetched
        let activeRequests = 0;
        let completedPages = 1; // Page 1 already counted
        let failedPages = [];

        function fetchNext() {
            if (self.isCancelled) return;

            while (activeRequests < maxConcurrent && nextPage <= totalPages) {
                const page = nextPage++;
                activeRequests++;

                const params = Object.assign({}, self.options.searchParams, {
                    content_type: 'content_' + self.options.contentType,
                    date_from: dateFrom,
                    date_to: dateTo,
                    page: page,
                    per_page: self.options.pageSize
                });

                const queryString = jQuery.param(params);

                const xhr = jQuery.ajax({
                    url: self.options.fetchDataUrl + '?' + queryString,
                    method: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        activeRequests--;

                        if (self.isCancelled) return;

                        if (response.status === 'error') {
                            failedPages.push(page);
                            console.error('Error fetching page ' + page + ': ' + (response.message || ''));
                        } else {
                            // Append rows
                            if (response.rows && response.rows.length > 0) {
                                self.allRows = self.allRows.concat(response.rows);
                            }
                            self.fetchedPages++;
                            completedPages++;

                            // Update progress (0-70% for data fetching)
                            const progressPercent = Math.round((self.fetchedPages / self.totalPages) * 70);
                            self.updateProgressBar(progressPercent);
                            self.updateProgressText(
                                'กำลังโหลดข้อมูล... หน้า ' + self.fetchedPages + '/' + self.totalPages +
                                ' (' + self.allRows.length.toLocaleString() + '/' + self.totalRows.toLocaleString() + ' รายการ) ' + progressPercent + '%'
                            );
                        }

                        // Check if all done
                        if (completedPages >= totalPages) {
                            if (failedPages.length === 0) {
                                self.generateAndDownload();
                            } else {
                                self.setStatus('เกิดข้อผิดพลาดในการดึงข้อมูลบางหน้า กรุณาลองใหม่', 'alert-danger');
                                self.toggleLoading(false);
                            }
                        } else {
                            fetchNext();
                        }
                    },
                    error: function(xhr, status, error) {
                        activeRequests--;

                        if (self.isCancelled) return;
                        if (status === 'abort') return;

                        failedPages.push(page);
                        completedPages++;
                        console.error('Failed to fetch page ' + page + ': ' + error);

                        // Check if all done
                        if (completedPages >= totalPages) {
                            if (failedPages.length === 0) {
                                self.generateAndDownload();
                            } else {
                                self.setStatus('เกิดข้อผิดพลาดในการดึงข้อมูลบางหน้า กรุณาลองใหม่', 'alert-danger');
                                self.toggleLoading(false);
                            }
                        } else {
                            fetchNext();
                        }
                    }
                });

                self.activeRequests.push(xhr);
            }
        }

        fetchNext();
    }

    generateAndDownload() {
        const self = this;

        if (this.isCancelled) return;

        if (this.allRows.length === 0) {
            this.setStatus('ไม่พบข้อมูลสำหรับ Export', 'alert-warning');
            this.toggleLoading(false);
            $('#' + this.options.contentType + 'ExportFormState').show();
            $('#' + this.options.contentType + 'ExportSuccessState').hide();
            $('#' + this.options.contentType + 'ExportInitialFooter').show();
            return;
        }

        // Step 1: Create XLSX (70-85%)
        this.updateProgressBar(75);
        this.updateProgressText('กำลังสร้างไฟล์ Excel... 75%');

        // Use setTimeout to allow UI to update
        setTimeout(function() {
            try {
                const wb = XLSX.utils.book_new();
                const ws = XLSX.utils.aoa_to_sheet([self.allHeaders].concat(self.allRows));

                // Set column widths
                const colWidths = self.allHeaders.map(function(h) {
                    return { wch: Math.max(h.length * 2, 15) };
                });
                ws['!cols'] = colWidths;

                // Excel sheet name max 31 chars, only valid chars
                var sheetName = self.baseFileName.replace(/[\[\]\*\?\/\\:]/g, '').substring(0, 31);
                XLSX.utils.book_append_sheet(wb, ws, sheetName);

                // Generate XLSX binary
                self.updateProgressBar(85);
                self.updateProgressText('กำลังสร้างไฟล์ Excel... 85%');

                var wbOut = XLSX.write(wb, { bookType: 'xlsx', type: 'array' });

                // Step 2: Create ZIP (85-95%)
                self.updateProgressBar(90);
                self.updateProgressText('กำลังบีบอัดไฟล์ ZIP... 90%');

                setTimeout(function() {
                    try {
                        var zip = new JSZip();
                        var timestamp = self.formatDateForFilename(new Date());
                        var xlsxFileName = self.baseFileName + '_' + timestamp + '.xlsx';
                        var zipFileName = self.baseFileName + '_' + timestamp + '.zip';

                        zip.file(xlsxFileName, wbOut);

                        self.updateProgressBar(95);
                        self.updateProgressText('กำลังบีบอัดไฟล์ ZIP... 95%');

                        zip.generateAsync({ type: 'blob' }).then(function(content) {
                            // Step 3: Download (95-100%)
                            self.updateProgressBar(100);
                            self.updateProgressText('กำลังดาวน์โหลด... 100%');

                            saveAs(content, zipFileName);

                            // Show success
                            self.showSuccessState();

                            self.updateProgressText('Export เสร็จสมบูรณ์! ดาวน์โหลด ' + self.allRows.length.toLocaleString() + ' รายการ');

                            self.toggleLoading(false);
                        }).catch(function(err) {
                            self.setStatus('เกิดข้อผิดพลาดในการสร้างไฟล์ ZIP: ' + err.message, 'alert-danger');
                            self.toggleLoading(false);
                        });
                    } catch (err) {
                        self.setStatus('เกิดข้อผิดพลาดในการสร้างไฟล์ ZIP: ' + err.message, 'alert-danger');
                        self.toggleLoading(false);
                    }
                }, 100);

            } catch (err) {
                self.setStatus('เกิดข้อผิดพลาดในการสร้างไฟล์ Excel: ' + err.message, 'alert-danger');
                self.toggleLoading(false);
            }
        }, 100);
    }

    formatDateForFilename(date) {
        var y = date.getFullYear();
        var m = String(date.getMonth() + 1).padStart(2, '0');
        var d = String(date.getDate()).padStart(2, '0');
        var h = String(date.getHours()).padStart(2, '0');
        var min = String(date.getMinutes()).padStart(2, '0');
        var s = String(date.getSeconds()).padStart(2, '0');
        return y + m + d + '_' + h + min + s + 's';
    }
}

// Make it available globally
window.AsyncExportModal = AsyncExportModal;
