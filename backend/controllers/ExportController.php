<?php

namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\AccessRule;
use backend\components\PermissionAccess;
use backend\components\ContentAsyncExportService;
use common\components\DatabaseJobMappingService;
use common\jobs\ExportJob;
use yii\web\Response;

class ExportController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'ruleConfig' => [
                    'class' => AccessRule::className()
                ],
                'rules' => [
                    [
                        'actions' => ['index', 'download', 'delete', 'cancel', 'start', 'status'],
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) {
                            return PermissionAccess::BackendAccess('content_list', 'controller');
                        }
                    ],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $userId = Yii::$app->user->identity->id;
        $jobs = ContentAsyncExportService::getAllJobsForUser($userId);

        return $this->render('index', [
            'jobs' => $jobs,
        ]);
    }

    public function actionStart()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $contentType = Yii::$app->request->post('content_type');
        $dateFrom = Yii::$app->request->post('date_from');
        $dateTo = Yii::$app->request->post('date_to');
        
        error_log("Export request: content_type=$contentType, date_from=$dateFrom, date_to=$dateTo");
        
        if (empty($dateFrom) || empty($dateTo)) {
            error_log("Export request failed: missing dates");
            return [
                'status' => 'error',
                'message' => 'กรุณาเลือกช่วงวันที่ให้ครบถ้วน',
            ];
        }

        if (empty($contentType)) {
            return [
                'status' => 'error',
                'message' => 'ไม่พบประเภทข้อมูลที่ต้องการ Export',
            ];
        }

        // Get filters based on content type
        $filters = $this->getExportFilters($contentType);
        $filters['date_from'] = $dateFrom;
        $filters['date_to'] = $dateTo;

        $exportJob = new ExportJob([
            'typeKey' => $contentType,
            'filters' => $filters,
            'userId' => Yii::$app->user->identity->id,
            'baseFileName' => $this->getBaseFileName($contentType),
        ]);
        
        // Push the job to queue
        $jobId = Yii::$app->queue->push($exportJob);
        
        // Make sure queue job ID is strictly saved as an integer or string representation of it
        $queueJobIdStr = (string)$jobId;
        
        // Also save a fallback mapping in ContentAsyncExportService for direct lookups
        $service = new ContentAsyncExportService();
        $initialJobHash = $exportJob->getJobHash();
        
        // The export job hasn't started yet, but we create a tracking record for it
        $userId = Yii::$app->user->identity->id;
        $userEmail = Yii::$app->user->identity->email;
        $placeholderJob = $service->createJob($contentType, $filters, $userId, $userEmail);
        
        // Overwrite the placeholder job status to pending
        $placeholderJob['status'] = ContentAsyncExportService::STATUS_PENDING;
        $service->saveJob($placeholderJob);
        
        // Store mappings using database service
        $mappingService = new DatabaseJobMappingService();
        $success = $mappingService->storeMappings($queueJobIdStr, $initialJobHash, $placeholderJob['id']);
        
        if (!$success) {
            error_log("Warning: Failed to store job mappings in database: queueJobId=$queueJobIdStr, jobHash=$initialJobHash, exportJobId={$placeholderJob['id']}");
            error_log("Warning: Job mapping storage failed - UI polling may be affected");
        }

        return [
            'status' => 'success',
            'jobId' => $placeholderJob['id'],
            'message' => 'เริ่มสร้างไฟล์ export แล้ว',
        ];
    }

    public function actionStatus()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        $jobId = Yii::$app->request->get('jobId');
        
        if (empty($jobId)) {
            return [
                'status' => 'error',
                'message' => 'ไม่พบ Job ID',
            ];
        }

        try {
            $job = ContentAsyncExportService::getJob($jobId);
            
            if (empty($job)) {
                return [
                    'status' => 'error',
                    'message' => 'ไม่พบงาน Export ที่ต้องการ',
                ];
            }

            $response = [
                'status' => 'success',
                'job' => [
                    'id' => $job['id'],
                    'state' => $job['status'],
                    'progress' => 0,
                    'progressMessage' => $job['progress_message'] ?? 'กำลังดำเนินการ...',
                    'downloadReady' => false,
                    'downloadUrl' => null,
                    'errorMessage' => $job['error_message'] ?? null,
                ],
            ];

            if ($job['status'] === ContentAsyncExportService::STATUS_COMPLETED) {
                $response['job']['state'] = 'completed';
                $response['job']['progress'] = 100;
                $response['job']['progressMessage'] = 'Export เสร็จสมบูรณ์';
                $response['job']['downloadReady'] = true;
                $response['job']['downloadUrl'] = \yii\helpers\Url::to(['/export/download', 'jobId' => $jobId], true);
            } elseif ($job['status'] === ContentAsyncExportService::STATUS_FAILED) {
                $response['job']['state'] = 'failed';
                $response['job']['progressMessage'] = 'Export ล้มเหลว';
            } elseif ($job['status'] === ContentAsyncExportService::STATUS_PROCESSING) {
                $response['job']['state'] = 'processing';
                $totalRows = $job['total_rows'] ?? 0;
                $currentPart = $job['current_part'] ?? 0;
                $totalFiles = $job['total_files'] ?? 1;
                
                if ($totalFiles > 0) {
                    $progress = min(99, (int)(($currentPart / $totalFiles) * 100));
                    $response['job']['progress'] = $progress;
                }
            }
            
            return $response;
        } catch (\Exception $e) {
            return ['status' => 'failed', 'message' => 'ไม่สามารถตรวจสอบสถานะได้: ' . $e->getMessage()];
        }
    }

    public function actionDownload($jobId)
    {
        $job = ContentAsyncExportService::getJob($jobId);
        
        if (empty($job)) {
            throw new NotFoundHttpException('ไม่พบไฟล์ Export ที่ต้องการ');
        }

        // Check if user owns this job
        if ($job['created_by_user_id'] != Yii::$app->user->identity->id) {
            throw new \yii\web\ForbiddenHttpException('คุณไม่มีสิทธิ์เข้าถึงไฟล์นี้');
        }

        // Check if job is completed
        if ($job['status'] !== ContentAsyncExportService::STATUS_COMPLETED) {
            throw new \yii\web\BadRequestHttpException('ไฟล์ Export ยังไม่เสร็จสมบูรณ์');
        }

        // Check if file exists
        $zipPath = ContentAsyncExportService::getDownloadPath($job);
        if (empty($zipPath) || !is_file($zipPath)) {
            throw new NotFoundHttpException('ไม่พบไฟล์ที่ต้องการดาวน์โหลด');
        }

        // Check if expired
        if (isset($job['expires_at']) && strtotime($job['expires_at']) < time()) {
            throw new \yii\web\BadRequestHttpException('ไฟล์หมดอายุแล้ว');
        }

        return Yii::$app->response->sendFile($zipPath, $job['zip_file_name'], [
            'mimeType' => 'application/zip',
            'inline' => false
        ]);
    }

    public function actionDelete($jobId)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $job = ContentAsyncExportService::getJob($jobId);
        
        if (empty($job)) {
            return [
                'status' => 'error',
                'message' => 'ไม่พบไฟล์ Export ที่ต้องการ'
            ];
        }

        // Check if user owns this job
        if ($job['created_by_user_id'] != Yii::$app->user->identity->id) {
            return [
                'status' => 'error',
                'message' => 'คุณไม่มีสิทธิ์ลบไฟล์นี้'
            ];
        }

        // Check if job can be deleted (only completed jobs with files)
        if ($job['status'] !== ContentAsyncExportService::STATUS_COMPLETED) {
            return [
                'status' => 'error',
                'message' => 'ไม่สามารถลบงานที่สถานะ ' . $job['status'] . ' ได้'
            ];
        }

        if (empty($job['zip_file_name']) || empty($job['zip_path'])) {
            return [
                'status' => 'error',
                'message' => 'ไฟล์ถูกลบแล้วหรือไม่มีไฟล์อยู่'
            ];
        }

        $success = ContentAsyncExportService::deleteJob($jobId);

        if ($success) {
            return [
                'status' => 'success',
                'message' => 'ลบไฟล์สำเร็จ'
            ];
        } else {
            return [
                'status' => 'error',
                'message' => 'ไม่สามารถลบไฟล์ได้'
            ];
        }
    }

    public function actionCancel($jobId)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $job = ContentAsyncExportService::getJob($jobId);
        
        if (empty($job)) {
            return [
                'status' => 'error',
                'message' => 'ไม่พบงาน Export ที่ต้องการ'
            ];
        }

        // Check if user owns this job
        if ($job['created_by_user_id'] != Yii::$app->user->identity->id) {
            return [
                'status' => 'error',
                'message' => 'คุณไม่มีสิทธิ์ยกเลิกงานนี้'
            ];
        }

        // Check if job can be cancelled (only pending or processing)
        if (!in_array($job['status'], [ContentAsyncExportService::STATUS_PENDING, ContentAsyncExportService::STATUS_PROCESSING])) {
            return [
                'status' => 'error',
                'message' => 'ไม่สามารถยกเลิกงานที่สถานะ ' . $job['status'] . ' ได้'
            ];
        }

        $success = ContentAsyncExportService::cancelJob($jobId);

        if ($success) {
            return [
                'status' => 'success',
                'message' => 'ยกเลิกการ Export สำเร็จ'
            ];
        } else {
            return [
                'status' => 'error',
                'message' => 'ไม่สามารถยกเลิกการ Export ได้'
            ];
        }
    }

    private function getExportFilters($contentType)
    {
        $request = Yii::$app->request;
        $filters = [];
        
        // Get search model name based on content type
        $searchModelMap = [
            'content_plant' => 'ContentPlantSearch',
            'content_animal' => 'ContentAnimalSearch',
            'content_fungi' => 'ContentFungiSearch',
            'content_ecotourism' => 'ContentEcotourismSearch',
            'content_expert' => 'ContentExpertSearch',
            'content_product' => 'ContentProductSearch',
        ];
        
        $searchModelName = $searchModelMap[$contentType] ?? null;
        if (!$searchModelName) {
            return $filters;
        }
        
        // Get search parameters from POST
        $searchParams = $request->post($searchModelName, []);
        
        // Common filters
        if (!empty($searchParams['name'])) {
            $filters['name'] = $searchParams['name'];
        }
        if (!empty($searchParams['created_by_user_id'])) {
            $filters['created_by_user_id'] = $searchParams['created_by_user_id'];
        }
        if (!empty($searchParams['updated_by_user_id'])) {
            $filters['updated_by_user_id'] = $searchParams['updated_by_user_id'];
        }
        if (!empty($searchParams['approved_by_user_id'])) {
            $filters['approved_by_user_id'] = $searchParams['approved_by_user_id'];
        }
        if (!empty($searchParams['status'])) {
            $filters['status'] = $searchParams['status'];
        }
        if (!empty($searchParams['note'])) {
            $filters['note'] = $searchParams['note'];
        }
        
        // Content-type specific filters
        switch ($contentType) {
            case 'content_product':
                if (!empty($searchParams['product_category_id'])) {
                    $filters['product_category_id'] = $searchParams['product_category_id'];
                }
                break;
            case 'content_expert':
                if (!empty($searchParams['expert_category_id'])) {
                    $filters['expert_category_id'] = $searchParams['expert_category_id'];
                }
                break;
        }
        
        return $filters;
    }

    private function getBaseFileName($contentType)
    {
        $fileNames = [
            'content_plant' => 'รายงานข้อมูลพืช',
            'content_animal' => 'รายงานข้อมูลสัตว์',
            'content_fungi' => 'รายงานข้อมูลจุลินทรีย์',
            'content_ecotourism' => 'รายงานข้อมูลท่องเที่ยวเชิงนิเวศ',
            'content_expert' => 'รายงานผู้เชี่ยวชาญ',
            'content_product' => 'ข้อมูลผลิตภัณฑ์ชุมชน',
        ];
        
        return $fileNames[$contentType] ?? 'Export';
    }
}
