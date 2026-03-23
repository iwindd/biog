<?php

namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\filters\AccessRule;
use backend\components\PermissionAccess;
use backend\components\ContentAsyncExportService;
use yii\web\Response;

class ExportDownloadsController extends Controller
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
                        'actions' => ['index', 'download', 'delete', 'cancel'],
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
}
