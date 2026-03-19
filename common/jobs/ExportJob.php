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
        
        // Find existing mapping or create new job using database service
        $mappingService = new DatabaseJobMappingService();
        $exportJobId = $mappingService->getExportJobIdFromHash($jobHash);
        
        if ($exportJobId) {
            $job = \backend\components\ContentAsyncExportService::getJob($exportJobId);
            if (!$job) {
                // Mapping exists but job missing, create new one
                $job = $service->createJob($this->typeKey, $this->filters, $this->userId);
                // Update the mapping with new export job ID
                $queueJobId = $this->queueJobId ?? 'unknown';
                $updateSuccess = $mappingService->storeMappings($queueJobId, $jobHash, $job['id']);
                
                if (!$updateSuccess) {
                    // Try fallback service
                    try {
                        $fallbackService = new \common\components\JobMappingService();
                        $fallbackService->storeMappings($queueJobId, $jobHash, $job['id']);
                        error_log("Successfully updated mappings using fallback service");
                    } catch (\Exception $e) {
                        error_log("Failed to update mappings with both services: " . $e->getMessage());
                    }
                }
            }
        } else {
            $job = $service->createJob($this->typeKey, $this->filters, $this->userId);
            // Store mapping for this job
            $queueJobId = $this->queueJobId ?? 'unknown';
            $storeSuccess = $mappingService->storeMappings($queueJobId, $jobHash, $job['id']);
            
            if (!$storeSuccess) {
                // Try fallback service
                try {
                    $fallbackService = new \common\components\JobMappingService();
                    $fallbackService->storeMappings($queueJobId, $jobHash, $job['id']);
                    error_log("Successfully stored mappings using fallback service");
                } catch (\Exception $e) {
                    error_log("Failed to store mappings with both services: " . $e->getMessage());
                }
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
        $query = \backend\models\Content::find()->select([
            'content.id as content_id',
            'content.name',
            'content.type_id',
            'content_plant.other_name',
            'content_plant.features',
            'content_plant.benefit',
            'content_plant.found_source',
            'content_plant.common_name',
            'content_plant.scientific_name',
            'content_plant.family_name',
            'content.region_id',
            'content.province_id',
            'content.district_id',
            'content.subdistrict_id',
            'content.zipcode_id',
            'content.created_by_user_id',
            'content.approved_by_user_id',
            'content.status',
            'content.note',
            'content.created_at',
        ]);
        $query->leftJoin('content_plant', 'content_plant.content_id = content.id');
        $query->leftJoin('profile', 'profile.user_id = content.created_by_user_id');

        $query->andFilterWhere(['=', 'content.type_id', 1]);
        $query->andFilterWhere(['=', 'content.active', 1]);

        if (!empty($filters['name'])) {
            $query->andFilterWhere(['like', 'content.name', $filters['name']]);
        }

        if (!empty($filters['created_by_user_id'])) {
            $query->andFilterWhere(['=', 'created_by_user_id', $filters['created_by_user_id']]);
        }

        if (!empty($filters['updated_by_user_id'])) {
            $query->andFilterWhere(['=', 'updated_by_user_id', $filters['updated_by_user_id']]);
        }

        if (!empty($filters['approved_by_user_id'])) {
            $query->andFilterWhere(['=', 'approved_by_user_id', $filters['approved_by_user_id']]);
        }

        if (!empty($filters['note'])) {
            $query->andFilterWhere(['like', 'note', $filters['note']]);
        }

        if (!empty($filters['status'])) {
            $query->andFilterWhere(['like', 'status', $filters['status']]);
        }

        if (!empty($filters['updated_at'])) {
            $query->andFilterWhere(['like', 'updated_at', $filters['updated_at']]);
        }

        if (!empty($filters['date_from'])) {
            $query->andFilterWhere(['>=', 'content.created_at', $filters['date_from'] . ' 00:00:00']);
        }

        if (!empty($filters['date_to'])) {
            $query->andFilterWhere(['<=', 'content.created_at', $filters['date_to'] . ' 23:59:59']);
        }

        return $query;
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
