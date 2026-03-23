<?php

namespace console\controllers;

use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use backend\components\ContentAsyncExportService;
use fedemotta\cronjob\models\CronJob;

class ExportController extends Controller
{
    /**
     * Cleanup expired export files
     * This command should be run periodically (e.g., via cron job every hour)
     * 
     * Usage examples:
     * php yii export/cleanup                    # Normal cleanup
     * php yii export/cleanup --dry-run          # Show what would be deleted
     * php yii export/cleanup --verbose          # Show detailed output
     * php yii export/cleanup --force            # Force delete all jobs (even if not expired)
     * 
     * @param bool $dryRun Show what would be deleted without actually deleting
     * @param bool $verbose Show detailed output
     * @param bool $force Force delete all jobs regardless of expiry
     * @return int Exit code
     */
    public function actionCleanup($dryRun = false, $verbose = false, $force = false)
    {
        $startTime = microtime(true);
        $timestamp = date('Y-m-d H:i:s');
        
        $this->stdout("[{$timestamp}] Starting export cleanup...\n");
        
        if ($dryRun) {
            $this->stdout("[{$timestamp}] DRY RUN MODE - No files will be deleted\n", Console::FG_YELLOW);
        }
        
        if ($force) {
            $this->stdout("[{$timestamp}] FORCE MODE - Deleting all jobs regardless of expiry\n", Console::FG_RED);
        }
        
        if ($verbose) {
            $this->stdout("[{$timestamp}] Verbose mode enabled\n");
        }
        
        try {
            if ($force) {
                // Force delete all jobs
                $result = $this->forceCleanupAllJobs($verbose);
            } else {
                // Normal cleanup of expired jobs
                $result = ContentAsyncExportService::cleanupExpiredJobs();
                
                if ($verbose) {
                    $this->showDetailedResults($result, $dryRun);
                }
            }
            
            $endTime = microtime(true);
            $duration = round($endTime - $startTime, 2);
            
            $this->stdout("[{$timestamp}] Cleanup completed in {$duration} seconds\n");
            $this->stdout("[{$timestamp}] Deleted: {$result['deleted']} job(s)\n");
            
            if (!empty($result['errors'])) {
                $this->stdout("[{$timestamp}] Errors: " . count($result['errors']) . "\n", Console::FG_RED);
                if ($verbose) {
                    foreach ($result['errors'] as $error) {
                        $this->stderr("  ERROR: {$error}\n");
                    }
                }
            }
            
            // Log to system log
            error_log("[ExportCleanup] Completed: {$result['deleted']} deleted, " . count($result['errors']) . " errors, duration: {$duration}s");
            
            return ExitCode::OK;
            
        } catch (\Exception $e) {
            $errorMsg = "[{$timestamp}] CRITICAL ERROR during cleanup: " . $e->getMessage();
            $this->stderr($errorMsg . "\n", Console::FG_RED);
            error_log($errorMsg);
            error_log("[ExportCleanup] Exception trace: " . $e->getTraceAsString());
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }
    
    /**
     * Force cleanup all jobs regardless of expiry
     * @param bool $verbose Show detailed output
     * @return array Cleanup results
     */
    private function forceCleanupAllJobs($verbose = false)
    {
        $baseDir = ContentAsyncExportService::getBaseDirectory();
        if (!is_dir($baseDir)) {
            return ['deleted' => 0, 'errors' => ['Export directory not found']];
        }
        
        $deleted = 0;
        $errors = [];
        $files = glob($baseDir . DIRECTORY_SEPARATOR . '*.json');
        
        if ($verbose) {
            $this->stdout("[" . date('Y-m-d H:i:s') . "] Found " . count($files) . " job files to process\n");
        }
        
        foreach ($files as $file) {
            try {
                $content = file_get_contents($file);
                if ($content === false || $content === '') {
                    continue;
                }
                
                $job = json_decode($content, true);
                if (!is_array($job) || !isset($job['id'])) {
                    continue;
                }
                
                if ($verbose) {
                    $this->stdout("[" . date('Y-m-d H:i:s') . "] Processing job: {$job['id']} (created: {$job['created_at']})\n");
                }
                
                // Delete job directory and files
                $jobDir = ContentAsyncExportService::getJobDirectory($job['id']);
                if (is_dir($jobDir)) {
                    if ($verbose) {
                        $this->stdout("[" . date('Y-m-d H:i:s') . "] Deleting directory: {$jobDir}\n");
                    }
                    \yii\helpers\FileHelper::removeDirectory($jobDir);
                }
                
                // Delete job metadata file
                if (is_file($file)) {
                    if ($verbose) {
                        $this->stdout("[" . date('Y-m-d H:i:s') . "] Deleting metadata file: " . basename($file) . "\n");
                    }
                    unlink($file);
                }
                
                $deleted++;
                error_log("[ExportCleanup] Force deleted job: {$job['id']}");
                
            } catch (\Exception $e) {
                $errors[] = "Failed to cleanup job file {$file}: " . $e->getMessage();
                error_log("[ExportCleanup] Force cleanup error: " . $e->getMessage());
            }
        }
        
        return ['deleted' => $deleted, 'errors' => $errors];
    }
    
    /**
     * Show detailed cleanup results
     * @param array $result Cleanup results
     * @param bool $dryRun Whether this was a dry run
     */
    private function showDetailedResults($result, $dryRun = false)
    {
        $timestamp = date('Y-m-d H:i:s');
        
        if ($result['deleted'] > 0) {
            $action = $dryRun ? 'Would delete' : 'Deleted';
            $this->stdout("[{$timestamp}] {$action} {$result['deleted']} expired job(s)\n", Console::FG_GREEN);
        } else {
            $this->stdout("[{$timestamp}] No expired jobs found to cleanup\n", Console::FG_INFO);
        }
        
        // Show disk space statistics
        $exportDir = ContentAsyncExportService::getBaseDirectory();
        if (is_dir($exportDir)) {
            $totalSize = $this->getDirectorySize($exportDir);
            $totalSizeFormatted = $this->formatBytes($totalSize);
            $this->stdout("[{$timestamp}] Current export directory size: {$totalSizeFormatted}\n");
        }
    }
    
    /**
     * Get total size of directory
     * @param string $dir Directory path
     * @return int Size in bytes
     */
    private function getDirectorySize($dir)
    {
        $size = 0;
        $files = glob(rtrim($dir, '/') . '/*', GLOB_NOSORT | GLOB_NODIR);
        
        foreach ($files as $file) {
            $size += is_file($file) ? filesize($file) : $this->getDirectorySize($file);
        }
        
        return $size;
    }
    
    /**
     * Format bytes to human readable format
     * @param int $bytes Size in bytes
     * @return string Formatted size
     */
    private function formatBytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * Cron-managed export cleanup
     * This action is intended to be called by CronJob helper to ensure
     * only one instance runs at a time and execution is tracked.
     * 
     * Usage (via cron):
     * php yii export/cron-cleanup
     * 
     * @param bool $verbose Show detailed output
     * @param bool $dryRun Show what would be deleted without actually deleting
     * @param bool $force Force delete all jobs regardless of expiry
     * @return int Exit code
     */
    public function actionCronCleanup($verbose = false, $dryRun = false, $force = false)
    {
        $timestamp = date('Y-m-d H:i:s');
        
        // Use CronJob helper to prevent overlapping executions
        $command = CronJob::run($this->id, $this->action->id, 0, 1);
        
        if ($command === false) {
            $this->stdout("[{$timestamp}] Export cleanup already running or failed to acquire lock\n", Console::FG_YELLOW);
            return ExitCode::UNSPECIFIED_ERROR;
        }
        
        try {
            $this->stdout("[{$timestamp}] Starting cron-managed export cleanup\n");
            
            // Delegate to the existing cleanup logic
            $result = $this->actionCleanup($dryRun, $verbose, $force);
            
            $command->finish();
            $this->stdout("[{$timestamp}] Cron-managed export cleanup completed\n");
            
            return $result;
            
        } catch (\Exception $e) {
            $command->finish();
            $this->stderr("[{$timestamp}] CRITICAL ERROR during cron cleanup: " . $e->getMessage() . "\n", Console::FG_RED);
            error_log("[ExportCron] Exception: " . $e->getMessage());
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }

    /**
     * Default cron cleanup action (runs for today only)
     * This provides a simple entry point for hourly cron execution
     * 
     * Usage:
     * php yii export/cron-run
     * 
     * @return int Exit code
     */
    public function actionCronRun()
    {
        return $this->actionCronCleanup(false, false, false);
    }
}
