/**
 * AsyncExportModal - Client-side chunked export with memory-safe XLSX/ZIP generation
 *
 * Flow (memory-safe chunked approach):
 * 1. User selects date range and clicks "เริ่ม Export"
 * 2. Fetch page 1 to get total count and headers
 * 3. Calculate number of parts (each part = up to rowsPerPart rows)
 * 4. For each part:
 *    a. Fetch pages sequentially into chunkBuffer (up to rowsPerPart rows)
 *    b. Generate XLSX from chunkBuffer using SheetJS
 *    c. Add XLSX binary to ZIP
 *    d. Clear chunkBuffer and release references
 * 5. Generate ZIP and download via FileSaver
 *
 * Memory stays bounded at ~rowsPerPart rows regardless of total dataset size.
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
            pageSize: 2500,
            rowsPerPart: 10000, // Max rows per XLSX file
            largeDatasetThreshold: 50000
        }, options);

        this.modalId = this.options.contentType + 'ExportModal';

        // Chunked state
        this.chunkBuffer = [];   // Current chunk's rows (cleared after each XLSX generation)
        this.allHeaders = [];
        this.baseFileName = '';
        this.totalRows = 0;
        this.totalPages = 0;
        this.totalParts = 1;
        this.currentPart = 0;

        // Fetch state
        this.fetchedPages = 0;
        this.isCancelled = false;
        this.activeRequests = [];
        this._retryCount = {};

        // ZIP state
        this.zipInstance = null;
        this.zipPartCount = 0;

        // Date state (saved for re-fetch across chunks)
        this.dateFrom = '';
        this.dateTo = '';

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
        // Release memory
        this.chunkBuffer = [];
        if (this.zipInstance) {
            this.zipInstance = null;
        }
    }

    resetModal() {
        this.cancelExport();

        // Reset all state
        this.chunkBuffer = [];
        this.allHeaders = [];
        this.baseFileName = '';
        this.totalRows = 0;
        this.totalPages = 0;
        this.totalParts = 1;
        this.currentPart = 0;
        this.fetchedPages = 0;
        this.isCancelled = false;
        this.zipInstance = null;
        this.zipPartCount = 0;
        this._retryCount = {};
        this.dateFrom = '';
        this.dateTo = '';

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
        this.chunkBuffer = [];
        this.allHeaders = [];
        this.baseFileName = '';
        this.isCancelled = false;
        this.fetchedPages = 0;
        this.currentPart = 0;
        this.zipInstance = null;
        this.zipPartCount = 0;
        this._retryCount = {};
        this.dateFrom = dateFrom;
        this.dateTo = dateTo;

        // Check dependencies
        if (typeof XLSX === 'undefined') {
            console.error('XLSX library not found. Please refresh the page and try again.');
            this.setStatus('เกิดข้อผิดพลาดกรุณาลองใหม่อีกครั้งภายหลัง!', 'alert-danger');
            return;
        }

        if (typeof JSZip === 'undefined') {
            console.error('JSZip library not found. Please refresh the page and try again.');
            this.setStatus('เกิดข้อผิดพลาดกรุณาลองใหม่อีกครั้งภายหลัง!', 'alert-danger');
            return;
        }

        this.toggleLoading(true);
        this.showProgressState();
        this.updateProgressText('กำลังเตรียมดึงข้อมูล...');

        // Fetch first page to get total count and headers
        this.fetchMetadataPage();
    }

    /**
     * Fetch page 1 to get total count, total pages, and headers.
     * Then kick off chunk processing.
     */
    fetchMetadataPage() {
        const self = this;

        if (this.isCancelled) return;

        const params = Object.assign({}, this.options.searchParams, {
            content_type: 'content_' + this.options.contentType,
            date_from: this.dateFrom,
            date_to: this.dateTo,
            page: 1,
            per_page: this.options.pageSize
        });

        const queryString = jQuery.param(params);

        const xhr = jQuery.ajax({
            url: this.options.fetchDataUrl + '?' + queryString,
            method: 'GET',
            dataType: 'json',
            success: function(response) {
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

                // Store metadata
                self.totalRows = response.total;
                self.totalPages = response.total_pages;
                self.allHeaders = response.headers || [];
                self.baseFileName = response.base_file_name || 'Export';

                // Check for no data
                if (self.totalRows === 0) {
                    self.setStatus('ไม่พบข้อมูลตามเงื่อนไขที่เลือก', 'alert-warning');
                    self.toggleLoading(false);
                    $('#' + self.options.contentType + 'ExportFormState').show();
                    $('#' + self.options.contentType + 'ExportSuccessState').hide();
                    $('#' + self.options.contentType + 'ExportInitialFooter').show();
                    return;
                }

                // Show large dataset warning
                if (self.totalRows >= self.options.largeDatasetThreshold) {
                    const partCount = Math.ceil(self.totalRows / self.options.rowsPerPart);
                    if (!confirm('ข้อมูลมีจำนวนมาก (' + self.totalRows.toLocaleString() + ' รายการ)\nระบบจะแบ่งเป็น ' + partCount + ' ไฟล์ Excel ในไฟล์ ZIP\nอาจใช้เวลานานในการประมวลผล ต้องการดำเนินการต่อหรือไม่?')) {
                        self.isCancelled = true;
                        self.resetModal();
                        return;
                    }
                }

                // Calculate parts
                self.totalParts = Math.ceil(self.totalRows / self.options.rowsPerPart);

                // Add page 1 rows to first chunk buffer
                if (response.rows && response.rows.length > 0) {
                    self.chunkBuffer = self.chunkBuffer.concat(response.rows);
                }
                self.fetchedPages = 1;

                // Initialize ZIP
                self.zipInstance = new JSZip();

                // Start processing chunks
                self.processNextChunk();
            },
            error: function(xhr, status, error) {
                self.activeRequests = self.activeRequests.filter(function(r) { return r !== xhr; });

                if (self.isCancelled) return;
                if (status === 'abort') return;

                self._retryCount = self._retryCount || {};
                self._retryCount[1] = (self._retryCount[1] || 0) + 1;

                if (self._retryCount[1] <= 3) {
                    console.warn('Retrying page 1 (attempt ' + self._retryCount[1] + ')');
                    setTimeout(function() {
                        self.fetchMetadataPage();
                    }, 1000 * self._retryCount[1]);
                } else {
                    self.setStatus('เกิดข้อผิดพลาดในการดึงข้อมูล: ' + error, 'alert-danger');
                    self.toggleLoading(false);
                }
            }
        });

        this.activeRequests.push(xhr);
    }

    /**
     * Process the next chunk: fetch remaining pages for this chunk,
     * then generate XLSX part, then move to next chunk or finalize ZIP.
     */
    processNextChunk() {
        if (this.isCancelled) return;

        this.currentPart++;

        // Check if chunk buffer already has enough rows for this part
        if (this.chunkBuffer.length >= this.options.rowsPerPart || this.fetchedPages >= this.totalPages) {
            // Chunk buffer already full or all pages fetched — generate XLSX part now
            this.generatePartXlsx();
            return;
        }

        // Calculate how many pages we need to fetch for this chunk
        const rowsNeeded = this.options.rowsPerPart - this.chunkBuffer.length;
        const pagesRemaining = this.totalPages - this.fetchedPages;
        const pagesToFetch = Math.min(pagesRemaining, Math.ceil(rowsNeeded / this.options.pageSize));

        // Update progress text with part info
        const partInfo = this.totalParts > 1
            ? ' (ส่วนที่ ' + this.currentPart + '/' + this.totalParts + ')'
            : '';

        this.updateProgressText(
            'กำลังโหลดข้อมูล' + partInfo + '... หน้า ' + this.fetchedPages + '/' + this.totalPages 
        );

        // Fetch pages for this chunk sequentially
        this.fetchChunkPagesSequentially(this.fetchedPages + 1, pagesToFetch);
    }

    /**
     * Fetch pages one by one for the current chunk.
     * After all pages for this chunk are fetched, call generatePartXlsx().
     */
    fetchChunkPagesSequentially(startPage, pageCount) {
        if (this.isCancelled) return;

        let currentPage = startPage;
        let remaining = pageCount;
        const self = this;

        function fetchNext() {
            if (self.isCancelled || remaining <= 0 || currentPage > self.totalPages) {
                // Done fetching for this chunk — generate XLSX part
                self.generatePartXlsx();
                return;
            }

            // Check if chunk buffer already has enough rows
            if (self.chunkBuffer.length >= self.options.rowsPerPart) {
                // Chunk buffer is full — generate XLSX part
                self.generatePartXlsx();
                return;
            }

            const params = Object.assign({}, self.options.searchParams, {
                content_type: 'content_' + self.options.contentType,
                date_from: self.dateFrom,
                date_to: self.dateTo,
                page: currentPage,
                per_page: self.options.pageSize
            });

            const queryString = jQuery.param(params);

            const xhr = jQuery.ajax({
                url: self.options.fetchDataUrl + '?' + queryString,
                method: 'GET',
                dataType: 'json',
                success: function(response) {
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

                    // Append rows to chunk buffer
                    if (response.rows && response.rows.length > 0) {
                        self.chunkBuffer = self.chunkBuffer.concat(response.rows);
                    }
                    self.fetchedPages++;
                    remaining--;

                    // Update progress (0-60% for data fetching)
                    const fetchProgress = Math.round((self.fetchedPages / self.totalPages) * 60);
                    const partInfo = self.totalParts > 1
                        ? ' (ส่วนที่ ' + self.currentPart + '/' + self.totalParts + ')'
                        : '';

                    self.updateProgressBar(fetchProgress);
                    self.updateProgressText(
                        'กำลังโหลดข้อมูล' + partInfo + '... หน้า ' + self.fetchedPages + '/' + self.totalPages 
                    );

                    // Check if chunk buffer has enough rows or all pages fetched
                    if (self.chunkBuffer.length >= self.options.rowsPerPart || self.fetchedPages >= self.totalPages) {
                        self.generatePartXlsx();
                    } else {
                        currentPage++;
                        fetchNext();
                    }
                },
                error: function(xhr, status, error) {
                    self.activeRequests = self.activeRequests.filter(function(r) { return r !== xhr; });

                    if (self.isCancelled) return;
                    if (status === 'abort') return;

                    // Retry logic
                    self._retryCount = self._retryCount || {};
                    self._retryCount[currentPage] = (self._retryCount[currentPage] || 0) + 1;

                    if (self._retryCount[currentPage] <= 3) {
                        console.warn('Retrying page ' + currentPage + ' (attempt ' + self._retryCount[currentPage] + ')');
                        setTimeout(function() {
                            fetchNext(); // retry same page — note: remaining not decremented
                        }, 1000 * self._retryCount[currentPage]);
                    } else {
                        self.setStatus('เกิดข้อผิดพลาดในการดึงข้อมูลหน้า ' + currentPage + ': ' + error, 'alert-danger');
                        self.toggleLoading(false);
                    }
                }
            });

            self.activeRequests.push(xhr);
        }

        fetchNext();
    }

    /**
     * Generate one XLSX part from the current chunkBuffer,
     * add it to the ZIP, then clear chunkBuffer and process next chunk or finalize.
     */
    generatePartXlsx() {
        const self = this;

        if (this.isCancelled) return;

        if (this.chunkBuffer.length === 0) {
            // No data in this chunk — skip
            if (this.fetchedPages >= this.totalPages) {
                this.finalizeZip();
            } else {
                this.processNextChunk();
            }
            return;
        }

        this.zipPartCount++;
        const partNumber = this.zipPartCount;

        const partInfo = this.totalParts > 1
            ? ' (ไฟล์ที่ ' + partNumber + '/' + this.totalParts + ')'
            : '';
        this.updateProgressText('กำลังสร้างไฟล์ Excel' + partInfo + '... ');

        // Use setTimeout to allow UI to update
        setTimeout(function() {
            try {
                if (self.isCancelled) return;

                var wb = XLSX.utils.book_new();
                var ws = XLSX.utils.aoa_to_sheet([self.allHeaders].concat(self.chunkBuffer));

                // Set column widths
                const colWidths = self.allHeaders.map(function(h) {
                    return { wch: Math.max(h.length * 2, 15) };
                });
                ws['!cols'] = colWidths;

                // Excel sheet name max 31 chars, only valid chars
                var sheetName = self.baseFileName.replace(/[\[\]\*\?\/\\:]/g, '').substring(0, 31);
                XLSX.utils.book_append_sheet(wb, ws, sheetName);

                // Generate XLSX binary
                var wbOut = XLSX.write(wb, { bookType: 'xlsx', type: 'array' });

                // Build filename for this part
                var timestamp = self.formatDateForFilename(new Date());
                var xlsxFileName;
                if (self.totalParts > 1) {
                    xlsxFileName = self.baseFileName + '_' + timestamp + '_part' + partNumber + '.xlsx';
                } else {
                    xlsxFileName = self.baseFileName + '_' + timestamp + '.xlsx';
                }

                // Add to ZIP
                self.zipInstance.file(xlsxFileName, wbOut);

                // Release memory: null out workbook objects
                wb = null;
                ws = null;
                wbOut = null;

                // Clear chunk buffer to free memory
                var rowsInPart = self.chunkBuffer.length;
                self.chunkBuffer = [];

                console.log('Generated XLSX part ' + partNumber + ': ' + xlsxFileName + ' (' + rowsInPart + ' rows)');

                // Check if there are more pages to fetch
                if (self.fetchedPages >= self.totalPages) {
                    // All pages fetched — finalize ZIP
                    self.finalizeZip();
                } else {
                    // Process next chunk
                    self.processNextChunk();
                }

            } catch (err) {
                self.setStatus('เกิดข้อผิดพลาดในการสร้างไฟล์ Excel' + partInfo + ': ' + err.message, 'alert-danger');
                self.toggleLoading(false);
            }
        }, 50);
    }

    /**
     * Generate the ZIP blob and trigger download.
     */
    finalizeZip() {
        const self = this;

        if (this.isCancelled) return;

        if (this.zipPartCount === 0) {
            this.setStatus('ไม่พบข้อมูลสำหรับ Export', 'alert-warning');
            this.toggleLoading(false);
            $('#' + this.options.contentType + 'ExportFormState').show();
            $('#' + this.options.contentType + 'ExportSuccessState').hide();
            $('#' + this.options.contentType + 'ExportInitialFooter').show();
            return;
        }

        // Progress: 85-95% for ZIP generation
        this.updateProgressBar(90);
        this.updateProgressText('กำลังบีบอัดไฟล์ ZIP... 90%');

        setTimeout(function() {
            try {
                if (self.isCancelled) return;

                var timestamp = self.formatDateForFilename(new Date());
                var zipFileName = self.baseFileName + '_' + timestamp + '.zip';

                self.updateProgressBar(95);
                self.updateProgressText('กำลังบีบอัดไฟล์ ZIP... 95%');

                self.zipInstance.generateAsync({ type: 'blob' }).then(function(content) {
                    if (self.isCancelled) return;

                    // Download
                    self.updateProgressBar(100);
                    self.updateProgressText('กำลังดาวน์โหลด... 100%');

                    saveAs(content, zipFileName);

                    // Show success
                    self.showSuccessState();

                    var fileList = self.zipPartCount > 1
                        ? self.zipPartCount + ' ไฟล์ Excel '
                        : '';
                    self.updateProgressText(
                        'Export เสร็จสมบูรณ์! ดาวน์โหลด ' + fileList +
                        self.totalRows.toLocaleString() + ' รายการ'
                    );

                    self.toggleLoading(false);

                    // Release ZIP memory
                    self.zipInstance = null;
                }).catch(function(err) {
                    self.setStatus('เกิดข้อผิดพลาดในการสร้างไฟล์ ZIP: ' + err.message, 'alert-danger');
                    self.toggleLoading(false);
                });
            } catch (err) {
                self.setStatus('เกิดข้อผิดพลาดในการสร้างไฟล์ ZIP: ' + err.message, 'alert-danger');
                self.toggleLoading(false);
            }
        }, 50);
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
