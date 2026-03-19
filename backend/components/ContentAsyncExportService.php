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
    const CHUNK_SIZE = 500;

    public static function createJob($typeKey, array $filters, $userId = null)
    {
        $jobId = uniqid($typeKey . '_export_', true);
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
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
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
        for ($part = 1; $part <= $totalFiles; $part++) {
            $offset = ($part - 1) * self::CHUNK_SIZE;
            $rows = (clone $query)->offset($offset)->limit(self::CHUNK_SIZE)->asArray()->all();

            $job['current_part'] = $part;
            $percentage = self::calculateProgressPercentage($job);
            $job['progress_message'] = 'กำลังสร้างไฟล์ ' . $part . '/' . $totalFiles . ' (' . $percentage . '%)';
            $job['peak_memory_mb'] = max($job['peak_memory_mb'], self::getPeakMemoryMb());
            self::saveJob($job);

            $xlsxPath = $jobDir . DIRECTORY_SEPARATOR . $baseFileName . '_part_' . $part . '.xlsx';
            $chunkExporter($rows, $xlsxPath, $part, $totalFiles);
            $generatedFiles[] = $xlsxPath;

            unset($rows);
            gc_collect_cycles();
        }

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
            }
        }

        $zip->close();
    }

    protected static function ensureBaseDirectories()
    {
        FileHelper::createDirectory(self::getBaseDirectory());
    }

    protected static function prepareRuntime()
    {
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', '0');
        set_time_limit(0);
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
}
