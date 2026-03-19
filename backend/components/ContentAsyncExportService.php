<?php

namespace backend\components;

use Yii;
use ZipArchive;
use yii\helpers\FileHelper;

class ContentAsyncExportService
{
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const CHUNK_SIZE = 3000;
    const FILE_RETENTION_HOURS = 48;

    private static $mailerConfig = null;

    public static function createJob($typeKey, array $filters, $userId = null, $userEmail = null)
    {
        $jobId = uniqid($typeKey . '_export_', true);
        $createdAt = date('Y-m-d H:i:s');
        $expiresAt = date('Y-m-d H:i:s', strtotime($createdAt . ' +' . self::FILE_RETENTION_HOURS . ' hours'));
        

        error_log("ContentAsyncExportService createJob - userId: " . $userId . ", userEmail: " . $userEmail);
        $job = [
            'id' => $jobId,
            'type_key' => $typeKey,
            'filters' => $filters,
            'status' => self::STATUS_PENDING,
            'progress_message' => 'กำลังเตรียม export (0%)',
            'total_rows' => 0,
            'total_files' => 0,
            'chunk_size' => self::CHUNK_SIZE,
            'current_part' => 0,
            'peak_memory_mb' => 0,
            'zip_file_name' => '',
            'zip_path' => '',
            'download_ready' => false,
            'error_message' => '',
            'created_by_user_id' => $userId,
            'user_email' => $userEmail,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
            'expires_at' => $expiresAt,
        ];

        self::ensureBaseDirectories();
        self::saveJob($job);

        return $job;
    }

    public static function getJob($jobId)
    {
        $jobFile = self::getJobFile($jobId);
        if (!is_file($jobFile)) {
            return null;
        }

        $content = file_get_contents($jobFile);
        if ($content === false || $content === '') {
            return null;
        }

        $job = json_decode($content, true);
        return is_array($job) ? $job : null;
    }

    public static function saveJob(array $job)
    {
        $job['updated_at'] = date('Y-m-d H:i:s');
        file_put_contents(self::getJobFile($job['id']), json_encode($job, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }

    private static function calculateProgressPercentage($job)
    {
        if ($job['status'] === self::STATUS_PENDING) {
            return 0;
        }
        
        if ($job['status'] === self::STATUS_FAILED) {
            return null;
        }
        
        if ($job['status'] === self::STATUS_COMPLETED) {
            return 100;
        }
        
        if ($job['total_files'] === 0) {
            // Still calculating or initial phase
            if (strpos($job['progress_message'], 'คำนวณจำนวนข้อมูล') !== false) {
                return 5;
            }
            return 0;
        }
        
        if ($job['current_part'] === 0) {
            // Before file generation starts
            return 5;
        }
        
        if (strpos($job['progress_message'], 'บีบอัดไฟล์ ZIP') !== false) {
            return 90;
        }
        
        // File generation phase: 5% to 89%
        $fileProgress = ($job['current_part'] - 1) / $job['total_files'];
        $percentage = 5 + ($fileProgress * 84); // 84% to leave room for ZIP phase
        return min(89, (int)$percentage);
    }

    public static function processJob($jobId, $query, callable $chunkExporter, $baseFileName)
    {
        $job = self::getJob($jobId);

        if (empty($job)) {
            error_log("Export job not found: $jobId");
            return null;
        }

        if ($job['status'] === self::STATUS_COMPLETED || $job['status'] === self::STATUS_FAILED) {
            return $job;
        }

        self::prepareRuntime();
        $dispatcher = Yii::$app->has('log', true) ? Yii::$app->getLog() : null;
        if ($dispatcher !== null) {
            $dispatcher->targets = [];
        }

        $job['status'] = self::STATUS_PROCESSING;
        $job['progress_message'] = 'กำลังคำนวณจำนวนข้อมูล (5%)';
        $job['peak_memory_mb'] = self::getPeakMemoryMb();
        error_log("Export job $jobId: Starting processing");
        self::saveJob($job);

        $totalRows = (clone $query)->count();
        $job['total_rows'] = (int) $totalRows;

        if ($job['total_rows'] === 0) {
            $job['status'] = self::STATUS_FAILED;
            $job['error_message'] = 'ไม่พบข้อมูลตามเงื่อนไขที่เลือก';
            $job['progress_message'] = 'ไม่พบข้อมูล';
            self::saveJob($job);
            return $job;
        }

        $totalFiles = (int) ceil($job['total_rows'] / self::CHUNK_SIZE);
        $job['total_files'] = $totalFiles;
        self::saveJob($job);

        $jobDir = self::getJobDirectory($jobId);
        FileHelper::createDirectory($jobDir);

        $generatedFiles = [];
        $baseQuery = clone $query; // Clone once outside the loop
        
        for ($part = 1; $part <= $totalFiles; $part++) {
            $offset = ($part - 1) * self::CHUNK_SIZE;
            
            // Create a new query for each chunk to avoid memory buildup
            $chunkQuery = clone $baseQuery;
            $rows = $chunkQuery->offset($offset)->limit(self::CHUNK_SIZE)->asArray()->all();
            
            // Clear the query object immediately
            unset($chunkQuery);

            $job['current_part'] = $part;
            $percentage = self::calculateProgressPercentage($job);
            $job['progress_message'] = 'กำลังสร้างไฟล์ ' . $part . '/' . $totalFiles . ' (' . $percentage . '%)';
            $job['peak_memory_mb'] = max($job['peak_memory_mb'], self::getPeakMemoryMb());
            
            // Save job status less frequently with larger chunks to reduce I/O
            if ($part % 2 === 0 || $part === $totalFiles) {
                self::saveJob($job);
            }

            $xlsxPath = $jobDir . DIRECTORY_SEPARATOR . $baseFileName . '_part_' . $part . '.xlsx';
            $chunkExporter($rows, $xlsxPath, $part, $totalFiles);
            $generatedFiles[] = $xlsxPath;

            // Explicit cleanup of rows and force garbage collection
            unset($rows);
            
            // Force garbage collection less frequently with larger chunks
            if ($part % 3 === 0) {
                gc_collect_cycles();
            }
        }
        
        // Clear the base query object
        unset($baseQuery);
        
        // Final garbage collection before ZIP creation
        gc_collect_cycles();

        $job['progress_message'] = 'กำลังบีบอัดไฟล์ ZIP (90%)';
        $job['peak_memory_mb'] = max($job['peak_memory_mb'], self::getPeakMemoryMb());
        self::saveJob($job);

        $zipFileName = $baseFileName . '_' . date('Ymd_His') . '.zip';
        $zipPath = $jobDir . DIRECTORY_SEPARATOR . $zipFileName;
        self::createZip($zipPath, $generatedFiles);

        $job['zip_file_name'] = $zipFileName;
        $job['zip_path'] = $zipPath;
        $job['download_ready'] = true;
        $job['status'] = self::STATUS_COMPLETED;
        $job['progress_message'] = 'พร้อมดาวน์โหลด (100%)';
        $job['peak_memory_mb'] = max($job['peak_memory_mb'], self::getPeakMemoryMb());
        self::saveJob($job);

        // Send email notification
        self::sendExportCompletionEmail($job);

        return $job;
    }

    public static function failJob($jobId, $message)
    {
        $job = self::getJob($jobId);
        if (empty($job)) {
            return null;
        }

        $job['status'] = self::STATUS_FAILED;
        $job['error_message'] = $message;
        $job['progress_message'] = 'เกิดข้อผิดพลาด';
        $job['peak_memory_mb'] = self::getPeakMemoryMb();
        self::saveJob($job);

        return $job;
    }

    public static function getDownloadPath(array $job)
    {
        return !empty($job['zip_path']) ? $job['zip_path'] : null;
    }

    protected static function createZip($zipPath, array $files)
    {
        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('ไม่สามารถสร้างไฟล์ ZIP ได้');
        }

        foreach ($files as $file) {
            if (is_file($file)) {
                $zip->addFile($file, basename($file));
                // Clear file from memory after adding to ZIP
                clearstatcache(true, $file);
            }
        }

        $zip->close();
        
        // Force garbage collection after ZIP creation
        gc_collect_cycles();
    }

    protected static function ensureBaseDirectories()
    {
        FileHelper::createDirectory(self::getBaseDirectory());
    }

    protected static function prepareRuntime()
    {
        // Set higher memory limit for large exports
        ini_set('memory_limit', '1G');
        
        // Remove execution time limits
        ini_set('max_execution_time', '0');
        set_time_limit(0);
        
        // Disable unnecessary features for background processing
        if (function_exists('xdebug_disable')) {
            xdebug_disable();
        }
        
        // Force garbage collection at the start
        gc_collect_cycles();
    }

    protected static function getPeakMemoryMb()
    {
        return round(memory_get_peak_usage(true) / 1048576, 2);
    }

    protected static function getBaseDirectory()
    {
        return Yii::getAlias('@backend/runtime/export-jobs');
    }

    protected static function getJobDirectory($jobId)
    {
        return self::getBaseDirectory() . DIRECTORY_SEPARATOR . $jobId;
    }

    protected static function getJobFile($jobId)
    {
        return self::getBaseDirectory() . DIRECTORY_SEPARATOR . $jobId . '.json';
    }

    protected static function sendMail($job, $subject, $message) {
        $mailSender = \backend\models\Variables::find()->where(['key' => 'sender_mail'])->one();
        if (empty($mailSender)) {
            error_log("Export job {$job['id']}: Mail sender not configured");
            return false;
        }
        
        if (self::$mailerConfig === null) {
            self::$mailerConfig = require Yii::getAlias('@common/config/mailer.php');
        }

        // Add detailed logging for debugging
        error_log("Export job {$job['id']}: Attempting to send email to {$job['user_email']} from {$mailSender['value']}");
        
        try {
            $transport = new \Swift_SmtpTransport('smtp.gmail.com', 587, 'tls');
            $transport->setUsername('biogang.smtp@gmail.com');
            $transport->setPassword('nsrxdrammdozafgg');
            $transport->start();
            
            $swiftMailer = new \Swift_Mailer($transport);
            $swiftMessage = (new \Swift_Message($subject))
                ->setFrom([$mailSender['value'] => 'BIOGANG'])
                ->setTo($job['user_email'])
                ->setBody($message);
            
            $swiftResult = $swiftMailer->send($swiftMessage);
            $transport->stop();
            
            if ($swiftResult > 0) {
                error_log("Export job {$job['id']}: Direct SwiftMailer email sent successfully to {$job['user_email']}");
                return true;
            } else {
                error_log("Export job {$job['id']}: Direct SwiftMailer failed - no recipients accepted");
                return false;
            }
            
        } catch (\Swift_TransportException $e) {
            error_log("Export job {$job['id']}: SMTP Transport Error - " . $e->getMessage());
            return false;
        } catch (\Exception $e) {
            error_log("Export job {$job['id']}: General Email Error - " . $e->getMessage());
            return false;
        } 
    }

    public static function sendExportCompletionEmail($job)
    {
        if (empty($job['user_email'])) {
            error_log("Export job {$job['id']}: No email address provided, skipping notification");
            return false;
        }
        
        // Validate email format
        if (!filter_var($job['user_email'], FILTER_VALIDATE_EMAIL)) {
            error_log("Export job {$job['id']}: Invalid email format: {$job['user_email']}");
            return false;
        }
        
        try {
            $typeNames = [
                'content_plant' => 'พืช',
                'content_animal' => 'สัตว์',
                'content_fungi' => 'จุลินทรีย์',
                'content_product' => 'ผลิตภัณฑ์',
                'content_expert' => 'ภูมิปัญญา',
                'content_ecotourism' => 'แหล่งท่องเที่ยว',
            ];
            
            $typeName = isset($typeNames[$job['type_key']]) ? $typeNames[$job['type_key']] : 'ข้อมูล';
            $subject = 'BIOGANG: ไฟล์ Export ' . $typeName . ' พร้อมดาวน์โหลดแล้ว';
            
            // Get hostname and protocol
            $hostname = getenv('BACKEND_URL');
            if (empty($hostname)) {
                $hostname = 'http://localhost:8080/admin';
            }
            
            $downloadUrl = $hostname . '/export-downloads';
            $expiresAt = date('d/m/Y H:i', strtotime($job['expires_at']));
            
            $message = "เรียน ผู้ใช้งาน BIOGANG\n\n";
            $message .= "ไฟล์ Export ข้อมูล{$typeName} ของคุณพร้อมดาวน์โหลดแล้ว\n\n";
            $message .= "รายละเอียด:\n";
            $message .= "- จำนวนข้อมูล: " . number_format($job['total_rows']) . " รายการ\n";
            $message .= "- จำนวนไฟล์: {$job['total_files']} ไฟล์\n";
            $message .= "- ชื่อไฟล์: {$job['zip_file_name']}\n";
            $message .= "- วันหมดอายุ: {$expiresAt} (เก็บไว้ 48 ชั่วโมง)\n\n";
            $message .= "คลิกลิงก์ด้านล่างเพื่อดาวน์โหลดไฟล์:\n";
            $message .= $downloadUrl . "\n\n";
            $message .= "หมายเหตุ: ไฟล์จะถูกลบอัตโนมัติหลังจาก 48 ชั่วโมง\n\n";
            $message .= "ขอบคุณที่ใช้บริการ BIOGANG";
            
            self::sendMail($job, $subject, $message);
        } catch (\Exception $e) {
            error_log("Export job {$job['id']}: Failed to send email - " . $e->getMessage());
            return false;
        }
    }

    public static function getAllJobsForUser($userId)
    {
        $baseDir = self::getBaseDirectory();
        if (!is_dir($baseDir)) {
            return [];
        }

        $jobs = [];
        $files = glob($baseDir . DIRECTORY_SEPARATOR . '*.json');
        
        foreach ($files as $file) {
            $content = file_get_contents($file);
            if ($content === false || $content === '') {
                continue;
            }
            
            $job = json_decode($content, true);
            if (is_array($job) && isset($job['created_by_user_id']) && $job['created_by_user_id'] == $userId) {
                $jobs[] = $job;
            }
        }
        
        // Sort by created_at descending
        usort($jobs, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        
        return $jobs;
    }

    public static function cleanupExpiredJobs()
    {
        $baseDir = self::getBaseDirectory();
        if (!is_dir($baseDir)) {
            return ['deleted' => 0, 'errors' => []];
        }

        $now = time();
        $deleted = 0;
        $errors = [];
        $files = glob($baseDir . DIRECTORY_SEPARATOR . '*.json');
        
        foreach ($files as $file) {
            try {
                $content = file_get_contents($file);
                if ($content === false || $content === '') {
                    continue;
                }
                
                $job = json_decode($content, true);
                if (!is_array($job) || !isset($job['expires_at'])) {
                    continue;
                }
                
                $expiresAt = strtotime($job['expires_at']);
                if ($expiresAt < $now) {
                    // Delete job directory and files
                    $jobDir = self::getJobDirectory($job['id']);
                    if (is_dir($jobDir)) {
                        FileHelper::removeDirectory($jobDir);
                    }
                    
                    // Delete job metadata file
                    if (is_file($file)) {
                        unlink($file);
                    }
                    
                    $deleted++;
                    error_log("Cleaned up expired export job: {$job['id']}");
                }
            } catch (\Exception $e) {
                $errors[] = "Failed to cleanup job file {$file}: " . $e->getMessage();
                error_log("Cleanup error: " . $e->getMessage());
            }
        }
        
        return ['deleted' => $deleted, 'errors' => $errors];
    }

    public static function deleteJob($jobId)
    {
        try {
            // Delete job directory and files
            $jobDir = self::getJobDirectory($jobId);
            if (is_dir($jobDir)) {
                FileHelper::removeDirectory($jobDir);
            }
            
            // Delete job metadata file
            $jobFile = self::getJobFile($jobId);
            if (is_file($jobFile)) {
                unlink($jobFile);
            }
            
            return true;
        } catch (\Exception $e) {
            error_log("Failed to delete job {$jobId}: " . $e->getMessage());
            return false;
        }
    }
}
