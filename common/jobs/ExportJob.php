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
        error_log("ExportJob execute started");
        
        $service = new \backend\components\ContentAsyncExportService();
        $jobHash = $this->getJobHash();
        
        // Get user email
        $userEmail = null;
        if ($this->userId) {
            $user = \backend\models\Users::findOne($this->userId);
            if ($user && !empty($user->email)) {
                $userEmail = $user->email;
            }
        }
        

        // Find existing mapping or create new job using database service
        $mappingService = new DatabaseJobMappingService();
        $exportJobId = $mappingService->getExportJobIdFromHash($jobHash);
        
        if ($exportJobId) {
            $job = \backend\components\ContentAsyncExportService::getJob($exportJobId);
            if (!$job) {
                // Mapping exists but job missing, create new one
                $job = $service->createJob($this->typeKey, $this->filters, $this->userId, $userEmail);
                // Update the mapping with new export job ID
                $queueJobId = $this->queueJobId ?? 'unknown';
                $updateSuccess = $mappingService->storeMappings($queueJobId, $jobHash, $job['id']);
                
                if (!$updateSuccess) {
                    error_log("Failed to update job mappings in database: queueJobId=$queueJobId, jobHash=$jobHash, exportJobId={$job['id']}");
                    // Continue without mapping - job will still be created but may not be trackable
                }
            }
        } else {
            $job = $service->createJob($this->typeKey, $this->filters, $this->userId, $userEmail);
            // Store mapping for this job
            $queueJobId = $this->queueJobId ?? 'unknown';
            $storeSuccess = $mappingService->storeMappings($queueJobId, $jobHash, $job['id']);
            
            if (!$storeSuccess) {
                error_log("Failed to store job mappings in database: queueJobId=$queueJobId, jobHash=$jobHash, exportJobId={$job['id']}");
                // Continue without mapping - job will still be created but may not be trackable
            }
        }
        
        error_log("Export job executing with ID: " . $job['id']);
        error_log("Job mapping confirmed: jobHash=$jobHash, exportJobId={$job['id']}");
        
        try {
            $query = $this->buildPlantExportQuery($this->filters);
            error_log("Starting export job processing for job ID: " . $job['id']);
            $job = $service->processJob($job['id'], $query, [$this, 'generatePlantExportFile'], $this->baseFileName);
            error_log("Export job processing completed for job ID: " . $job['id'] . ", status: " . ($job['status'] ?? 'unknown'));
        } catch (\Exception $e) {
            error_log("Export job processing failed for job ID: " . $job['id'] . " - " . $e->getMessage());
            $service->failJob($job['id'], $e->getMessage());
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
