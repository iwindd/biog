<?php

namespace console\controllers;

use Yii;
use yii\console\Controller;
use backend\components\ContentAsyncExportService;

class ExportController extends Controller
{
    /**
     * Cleanup expired export files
     * This command should be run periodically (e.g., via cron job every hour)
     * 
     * Example usage:
     * php yii export/cleanup
     */
    public function actionCleanup()
    {
        $this->stdout("Starting export cleanup...\n");
        
        $result = ContentAsyncExportService::cleanupExpiredJobs();
        
        $this->stdout("Cleanup completed:\n");
        $this->stdout("- Deleted: {$result['deleted']} expired job(s)\n");
        
        if (!empty($result['errors'])) {
            $this->stdout("- Errors: " . count($result['errors']) . "\n");
            foreach ($result['errors'] as $error) {
                $this->stderr("  ERROR: {$error}\n");
            }
        }
        
        return 0;
    }
}
