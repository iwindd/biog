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
                $query = null;
                $exporter = null;
                
                switch ($this->typeKey) {
                    case 'content_plant':
                        $query = \backend\components\BackendHelper::buildPlantExportQuery($this->filters);
                        $exporter = [$this, 'generatePlantExportFile'];
                        break;
                    case 'content_animal':
                        $query = \backend\components\BackendHelper::buildAnimalExportQuery($this->filters);
                        $exporter = [$this, 'generateAnimalExportFile'];
                        break;
                    case 'content_fungi':
                        $query = \backend\components\BackendHelper::buildFungiExportQuery($this->filters);
                        $exporter = [$this, 'generateFungiExportFile'];
                        break;
                    case 'content_ecotourism':
                        $query = \backend\components\BackendHelper::buildEcotourismExportQuery($this->filters);
                        $exporter = [$this, 'generateEcotourismExportFile'];
                        break;
                    case 'content_expert':
                        $query = \backend\components\BackendHelper::buildExpertExportQuery($this->filters);
                        $exporter = [$this, 'generateExpertExportFile'];
                        break;
                    case 'content_product':
                        $query = \backend\components\BackendHelper::buildProductExportQuery($this->filters);
                        $exporter = [$this, 'generateProductExportFile'];
                        break;
                    default:
                        throw new \Exception("Unknown export type: {$this->typeKey}");
                }

                error_log("[ExportJob] Starting export job processing for job ID: $exportJobId");
                $job = $service->processJob($exportJobId, $query, $exporter, $this->baseFileName);
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
            'รายงานพืช',
            'ข้อมูลพืชทั้งหมด (ไฟล์ ' . $part . '/' . $totalFiles . ')'
        );

        unset($exportRows, $headers, $rows);
        gc_collect_cycles();
    }

    public function generateAnimalExportFile($rows, $filePath, $part, $totalFiles)
    {
        $protocol = 'https://';
        $hostname = 'biog.local';
        
        if (class_exists('\Yii') && \Yii::$app && isset(\Yii::$app->request) && method_exists(\Yii::$app->request, 'getHostInfo')) {
            $protocol = stripos(\Yii::$app->request->getHostInfo(), 'https://') === 0 ? 'https://' : 'http://';
            $hostname = \Yii::$app->request->getHostName();
            if (empty($hostname)) {
                $hostname = 'localhost:8080';
            }
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
            $contentType = 'animal';
            if (isset($value['type_id'])) {
                try {
                    $contentType = \frontend\components\FrontendHelper::getContentTypeById($value['type_id']);
                } catch (\Exception $e) {
                    $contentType = 'animal';
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
            'รายงานสัตว์',
            'ข้อมูลสัตว์ทั้งหมด (ไฟล์ ' . $part . '/' . $totalFiles . ')'
        );

        unset($exportRows, $headers, $rows);
        gc_collect_cycles();
    }

    public function generateFungiExportFile($rows, $filePath, $part, $totalFiles)
    {
        $protocol = 'https://';
        $hostname = 'biog.local';
        
        if (class_exists('\Yii') && \Yii::$app && isset(\Yii::$app->request) && method_exists(\Yii::$app->request, 'getHostInfo')) {
            $protocol = stripos(\Yii::$app->request->getHostInfo(), 'https://') === 0 ? 'https://' : 'http://';
            $hostname = \Yii::$app->request->getHostName();
            if (empty($hostname)) {
                $hostname = 'localhost:8080';
            }
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
            $contentType = 'fungi';
            if (isset($value['type_id'])) {
                try {
                    $contentType = \frontend\components\FrontendHelper::getContentTypeById($value['type_id']);
                } catch (\Exception $e) {
                    $contentType = 'fungi';
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
            'รายงานจุลินทรีย์',
            'ข้อมูลจุลินทรีย์ทั้งหมด (ไฟล์ ' . $part . '/' . $totalFiles . ')'
        );

        unset($exportRows, $headers, $rows);
        gc_collect_cycles();
    }

    public function generateEcotourismExportFile($rows, $filePath, $part, $totalFiles)
    {
        $protocol = 'https://';
        $hostname = 'biog.local';
        
        if (class_exists('\Yii') && \Yii::$app && isset(\Yii::$app->request) && method_exists(\Yii::$app->request, 'getHostInfo')) {
            $protocol = stripos(\Yii::$app->request->getHostInfo(), 'https://') === 0 ? 'https://' : 'http://';
            $hostname = \Yii::$app->request->getHostName();
            if (empty($hostname)) {
                $hostname = 'localhost:8080';
            }
        }
        
        $headers = [
            'ชื่อเรื่อง',
            'ที่อยู่',
            'เบอร์โทรศัพท์',
            'ชื่อผู้ติดต่อ',
            'ข้อมูลการติดต่อ',
            'อธิบายการเดินทาง',
            'รายละเอียด',
            'ข้อมูลอื่นๆ',
            'ลิงก์',
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
            $contentType = 'ecotourism';
            if (isset($value['type_id'])) {
                try {
                    $contentType = \frontend\components\FrontendHelper::getContentTypeById($value['type_id']);
                } catch (\Exception $e) {
                    $contentType = 'ecotourism';
                }
            }
            
            $exportRows[] = [
                $value['name'],
                $value['address'],
                $value['phone'],
                $value['contact_name'],
                $value['contact'],
                \backend\components\BaseExportController::cleanText($value['travel_information']),
                \backend\components\BaseExportController::cleanText($value['description']),
                \backend\components\BaseExportController::cleanText($value['other_information']),
                $protocol . $hostname . '/content-' . $contentType . '/' . $value['content_id'],
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
            'รายงานท่องเที่ยว',
            'ข้อมูลแหล่งท่องเที่ยวเชิงนิเวศทั้งหมด (ไฟล์ ' . $part . '/' . $totalFiles . ')'
        );

        unset($exportRows, $headers, $rows);
        gc_collect_cycles();
    }

    public function generateExpertExportFile($rows, $filePath, $part, $totalFiles)
    {
        $protocol = 'https://';
        $hostname = 'biog.local';
        
        if (class_exists('\Yii') && \Yii::$app && isset(\Yii::$app->request) && method_exists(\Yii::$app->request, 'getHostInfo')) {
            $protocol = stripos(\Yii::$app->request->getHostInfo(), 'https://') === 0 ? 'https://' : 'http://';
            $hostname = \Yii::$app->request->getHostName();
            if (empty($hostname)) {
                $hostname = 'localhost:8080';
            }
        }
        
        $headers = [
            'ชื่อ',
            'นามสกุล',
            'วันเกิด',
            'ความเชี่ยวชาญ',
            'อาชีพ',
            'เลขบัตรประชาชน',
            'เบอร์โทรศัพท์',
            'ที่อยู่',
            'หมวดหมู่ผู้เชี่ยวชาญ',
            'ลิงก์',
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
            $contentType = 'expert';
            if (isset($value['type_id'])) {
                try {
                    $contentType = \frontend\components\FrontendHelper::getContentTypeById($value['type_id']);
                } catch (\Exception $e) {
                    $contentType = 'expert';
                }
            }
            
            $exportRows[] = [
                $value['expert_firstname'] ?? '',
                $value['expert_lastname'] ?? '',
                $value['expert_birthdate'] ?? '',
                $value['expert_expertise'] ?? '',
                $value['expert_occupation'] ?? '',
                $value['expert_card_id'] ?? '',
                $value['phone'] ?? '',
                \backend\components\BaseExportController::cleanText($value['address'] ?? ''),
                \backend\components\BackendHelper::getNameCategoryExpert($value['expert_category_id']),
                $protocol . $hostname . '/content-' . $contentType . '/' . $value['content_id'],
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
            'รายงานภูมิปัญญา',
            'ข้อมูลภูมิปัญญาทั้งหมด (ไฟล์ ' . $part . '/' . $totalFiles . ')'
        );

        unset($exportRows, $headers, $rows);
        gc_collect_cycles();
    }

    public function generateProductExportFile($rows, $filePath, $part, $totalFiles)
    {
        $protocol = 'https://';
        $hostname = 'biog.local';
        
        if (class_exists('\Yii') && \Yii::$app && isset(\Yii::$app->request) && method_exists(\Yii::$app->request, 'getHostInfo')) {
            $protocol = stripos(\Yii::$app->request->getHostInfo(), 'https://') === 0 ? 'https://' : 'http://';
            $hostname = \Yii::$app->request->getHostName();
            if (empty($hostname)) {
                $hostname = 'localhost:8080';
            }
        }
        
        $headers = [
            'ชื่อเรื่อง',
            'หมวดหมู่ผลิตภัณฑ์',
            'จุดเด่น/ประโยชน์',
            'วัตถุดิบหลัก',
            'แหล่งวัตถุดิบ',
            'ราคาขาย',
            'สถานที่ผลิต/จำหน่าย',
            'ที่อยู่',
            'เบอร์โทรศัพท์',
            'รายละเอียดเพิ่มเติม',
            'แหล่งที่พบ',
            'ข้อมูลการติดต่อ',
            'รายละเอียด',
            'ข้อมูลอื่นๆ',
            'ลิงก์',
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
            $contentType = 'product';
            if (isset($value['type_id'])) {
                try {
                    $contentType = \frontend\components\FrontendHelper::getContentTypeById($value['type_id']);
                } catch (\Exception $e) {
                    $contentType = 'product';
                }
            }
            
            $exportRows[] = [
                $value['name'],
                \backend\components\BackendHelper::getNameCategoryProduct($value['product_category_id']),
                \backend\components\BaseExportController::cleanText($value['product_features']),
                \backend\components\BaseExportController::cleanText($value['product_main_material']),
                \backend\components\BaseExportController::cleanText($value['product_sources_material']),
                $value['product_price'],
                \backend\components\BaseExportController::cleanText($value['product_distribution_location']),
                \backend\components\BaseExportController::cleanText($value['product_address']),
                $value['product_phone'],
                \backend\components\BaseExportController::cleanText($value['product_other_info']),
                \backend\components\BaseExportController::cleanText($value['found_source']),
                \backend\components\BaseExportController::cleanText($value['contact']),
                \backend\components\BaseExportController::cleanText($value['description']),
                \backend\components\BaseExportController::cleanText($value['content_other_info']),
                $protocol . $hostname . '/content-' . $contentType . '/' . $value['content_id'],
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
            'รายงานผลิตภัณฑ์',
            'ข้อมูลผลิตภัณฑ์ทั้งหมด (ไฟล์ ' . $part . '/' . $totalFiles . ')'
        );

        unset($exportRows, $headers, $rows);
        gc_collect_cycles();
    }
}
