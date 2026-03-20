<?php

namespace common\jobs;

use yii\queue\JobInterface;
use yii\base\BaseObject;
use common\components\DatabaseJobMappingService;

class ExportJob extends BaseObject implements JobInterface
{
    public $typeKey;
    public $filters;
    public $userId;
    public $baseFileName;
    public $queueJobId;

    public function execute($queue)
    {
        $queueJobId = $this->queueJobId ?? 'unknown';
        $jobHash = null;
        $exportJobId = null;
        
        try {
            error_log("[ExportJob] Starting execution - queueJobId: $queueJobId, typeKey: {$this->typeKey}");
            
            $service = new \backend\components\ContentAsyncExportService();
            $jobHash = $this->getJobHash();
            
            error_log("[ExportJob] Generated jobHash: $jobHash");
            
            // Get user email
            $userEmail = null;
            if ($this->userId) {
                try {
                    $user = \backend\models\Users::findOne($this->userId);
                    if ($user && !empty($user->email)) {
                        $userEmail = $user->email;
                        error_log("[ExportJob] User email found: $userEmail");
                    } else {
                        error_log("[ExportJob] User found but no email for userId: {$this->userId}");
                    }
                } catch (\Exception $e) {
                    error_log("[ExportJob] Error fetching user: " . $e->getMessage());
                    // Continue without email
                }
            }
            
            // Find existing mapping or create new job using database service
            $mappingService = new DatabaseJobMappingService();
            $exportJobId = $mappingService->getExportJobIdFromHash($jobHash);
            
            if ($exportJobId) {
                error_log("[ExportJob] Found existing exportJobId from hash: $exportJobId");
                $job = \backend\components\ContentAsyncExportService::getJob($exportJobId);
                if (!$job) {
                    error_log("[ExportJob] Mapping exists but job missing, creating new job");
                    // Mapping exists but job missing, create new one
                    $job = $service->createJob($this->typeKey, $this->filters, $this->userId, $userEmail);
                    // Update the mapping with new export job ID
                    $updateSuccess = $mappingService->storeMappings($queueJobId, $jobHash, $job['id']);
                    
                    if (!$updateSuccess) {
                        error_log("[ExportJob] WARNING: Failed to update job mappings in database: queueJobId=$queueJobId, jobHash=$jobHash, exportJobId={$job['id']}");
                        // Continue without mapping - job will still be created but may not be trackable
                    } else {
                        error_log("[ExportJob] Successfully updated job mapping");
                    }
                } else {
                    error_log("[ExportJob] Using existing job: {$job['id']}, status: {$job['status']}");
                }
            } else {
                error_log("[ExportJob] No existing mapping found, creating new job");
                $job = $service->createJob($this->typeKey, $this->filters, $this->userId, $userEmail);
                // Store mapping for this job
                $storeSuccess = $mappingService->storeMappings($queueJobId, $jobHash, $job['id']);
                
                if (!$storeSuccess) {
                    error_log("[ExportJob] WARNING: Failed to store job mappings in database: queueJobId=$queueJobId, jobHash=$jobHash, exportJobId={$job['id']}");
                    // Continue without mapping - job will still be created but may not be trackable
                } else {
                    error_log("[ExportJob] Successfully stored job mapping");
                }
            }
            
            $exportJobId = $job['id'];
            error_log("[ExportJob] Export job executing with ID: $exportJobId");
            error_log("[ExportJob] Job mapping confirmed: jobHash=$jobHash, exportJobId=$exportJobId");
            
            try {
                $query = $this->buildPlantExportQuery($this->filters);
                error_log("[ExportJob] Starting export job processing for job ID: $exportJobId");
                $job = $service->processJob($exportJobId, $query, [$this, 'generatePlantExportFile'], $this->baseFileName);
                error_log("[ExportJob] Export job processing completed for job ID: $exportJobId, status: " . ($job['status'] ?? 'unknown'));
            } catch (\Exception $e) {
                error_log("[ExportJob] Export job processing failed for job ID: $exportJobId - " . $e->getMessage());
                error_log("[ExportJob] Exception trace: " . $e->getTraceAsString());
                $service->failJob($exportJobId, $e->getMessage());
                // Re-throw to let queue system know the job failed
                throw $e;
            }
            
            error_log("[ExportJob] Successfully completed execution - queueJobId: $queueJobId, exportJobId: $exportJobId");
            
        } catch (\Exception $e) {
            // Catch any exception that occurs anywhere in the execute method
            error_log("[ExportJob] CRITICAL ERROR in execute() - queueJobId: $queueJobId");
            error_log("[ExportJob] Error message: " . $e->getMessage());
            error_log("[ExportJob] Error file: " . $e->getFile() . ":" . $e->getLine());
            error_log("[ExportJob] Stack trace: " . $e->getTraceAsString());
            
            // Try to fail the job if we have an exportJobId
            if ($exportJobId) {
                try {
                    $service = new \backend\components\ContentAsyncExportService();
                    $service->failJob($exportJobId, $e->getMessage());
                    error_log("[ExportJob] Marked job as failed: $exportJobId");
                } catch (\Exception $failException) {
                    error_log("[ExportJob] Failed to mark job as failed: " . $failException->getMessage());
                }
            }
            
            // Re-throw the exception so the queue system can handle it properly
            throw $e;
        }
    }

        public function getJobHash()
    {
        // Convert to array and sort to ensure consistent hashing
        $filters = $this->filters ?: [];
        ksort($filters);
        
        $data = [
            'typeKey' => (string)$this->typeKey,
            'filters' => $filters,
            'userId' => (int)$this->userId,
            'baseFileName' => (string)$this->baseFileName,
        ];
        
        return md5(json_encode($data));
    }


    private function buildPlantExportQuery($filters = [])
    {
        return \backend\components\BackendHelper::buildPlantExportQuery($filters);
    }

    public function generatePlantExportFile($rows, $filePath, $part, $totalFiles)
    {
        // Get protocol and hostname for generating links
        $protocol = 'https://';
        $hostname = 'biog.local'; // Default hostname, should be configured
        
        // Try to get from Yii app if available and it's a web request
        if (class_exists('\Yii') && \Yii::$app && isset(\Yii::$app->request) && method_exists(\Yii::$app->request, 'getHostInfo')) {
            $protocol = stripos(\Yii::$app->request->getHostInfo(), 'https://') === 0 ? 'https://' : 'http://';
            $hostname = \Yii::$app->request->getHostName();
            if (empty($hostname)) {
                $hostname = 'localhost:8080';
            }
        } else {
            // Console environment - use default or configured values
            // In production, you might want to read this from config or environment variables
            $hostname = 'biog.local'; // Update this to your actual domain
        }

        $headers = [
            'ชื่อเรื่อง',
            'ชื่ออื่น',
            'ลิงก์',
            'ลักษณะ',
            'ประโยชน์',
            'แหล่งที่พบ',
            'ชื่อสามัญ',
            'ชื่อวิทยาศาสตร์',
            'ชื่อวงศ์',
            'ภาค',
            'จังหวัด',
            'อำเภอ',
            'ตำบล',
            'รหัสไปรษณีย์',
            'ชื่อผู้นำเข้าข้อมูล',
            'ชื่อผู้อนุมัติข้อมูล',
            'สถานะ',
            'หมายเหตุ',
            'วันที่นำเข้าข้อมูล',
        ];

        $exportRows = [];
        foreach ($rows as $value) {
            $contentType = 'plant'; // Default fallback
            if (isset($value['type_id'])) {
                try {
                    $contentType = \frontend\components\FrontendHelper::getContentTypeById($value['type_id']);
                } catch (\Exception $e) {
                    $contentType = 'plant'; // Fallback if helper fails
                }
            }
            
            $exportRows[] = [
                $value['name'],
                $value['other_name'],
                $protocol . $hostname . '/content-' . $contentType . '/' . $value['content_id'],
                \backend\components\BaseExportController::cleanText($value['features']),
                \backend\components\BaseExportController::cleanText($value['benefit']),
                $value['found_source'],
                $value['common_name'],
                $value['scientific_name'],
                $value['family_name'],
                \backend\components\BackendHelper::getNameRegion($value['region_id']),
                \backend\components\BackendHelper::getNameProvince($value['province_id']),
                \backend\components\BackendHelper::getNameDistrict($value['district_id']),
                \backend\components\BackendHelper::getNameSubdistrict($value['subdistrict_id']),
                \backend\components\BackendHelper::getNameZipcode($value['zipcode_id']),
                \backend\components\BackendHelper::getName($value['created_by_user_id']),
                \backend\components\BackendHelper::getName($value['approved_by_user_id']),
                ucfirst($value['status']),
                $value['note'],
                $value['created_at'],
            ];
        }

        \backend\components\BaseExportController::saveRowsToStreamingXlsx(
            $filePath,
            $headers,
            $exportRows,
            'รายงานข้อมูลพืช',
            'ข้อมูลพืชทั้งหมด (ไฟล์ ' . $part . '/' . $totalFiles . ')'
        );

        unset($exportRows, $headers, $rows);
        gc_collect_cycles();
    }
}
