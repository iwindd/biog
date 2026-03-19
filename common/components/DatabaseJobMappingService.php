<?php

namespace common\components;

use Yii;
use yii\base\Component;
use yii\db\Exception;

/**
 * DatabaseJobMappingService handles all export job mapping operations using database
 * with proper transaction handling to prevent race conditions and ensure data consistency.
 */
class DatabaseJobMappingService extends Component
{
    /**
     * Atomically store all mappings for a job
     * @param string $queueJobId Queue job ID
     * @param string $jobHash Job hash
     * @param string $exportJobId Export job ID
     * @return bool Success status
     */
    public function storeMappings($queueJobId, $jobHash, $exportJobId)
    {
        // Input validation
        if (empty($queueJobId) || empty($jobHash) || empty($exportJobId)) {
            error_log("Invalid input: queueJobId, jobHash, and exportJobId cannot be empty");
            return false;
        }
        
        if (!is_string($queueJobId) || !is_string($jobHash) || !is_string($exportJobId)) {
            error_log("Invalid input type: all parameters must be strings");
            return false;
        }
        
        // Length validation to prevent database errors
        if (strlen($queueJobId) > 255 || strlen($jobHash) > 255 || strlen($exportJobId) > 255) {
            error_log("Invalid input length: parameters exceed 255 character limit");
            return false;
        }
        
        $transaction = Yii::$app->db->beginTransaction();
        
        try {
            // First try to find exact match on both queue_job_id AND job_hash
            $existing = Yii::$app->db->createCommand("
                SELECT id FROM {{%job_mapping}} 
                WHERE queue_job_id = :queueJobId AND job_hash = :jobHash
            ")->bindValues([
                ':queueJobId' => $queueJobId,
                ':jobHash' => $jobHash
            ])->queryOne();
            
            if ($existing) {
                // Update existing exact match
                $result = Yii::$app->db->createCommand()->update('{{%job_mapping}}', [
                    'export_job_id' => $exportJobId,
                    'updated_at' => new \yii\db\Expression('CURRENT_TIMESTAMP')
                ], 'id = :id', ['id' => $existing['id']])->execute();
            } else {
                // Try to find existing record with same job_hash to update
                $hashExisting = Yii::$app->db->createCommand("
                    SELECT id FROM {{%job_mapping}} 
                    WHERE job_hash = :jobHash LIMIT 1
                ")->bindValue(':jobHash', $jobHash)->queryOne();
                
                if ($hashExisting) {
                    // Update the existing record with new queue_job_id and export_job_id
                    $result = Yii::$app->db->createCommand()->update('{{%job_mapping}}', [
                        'queue_job_id' => $queueJobId,
                        'export_job_id' => $exportJobId,
                        'updated_at' => new \yii\db\Expression('CURRENT_TIMESTAMP')
                    ], 'id = :id', ['id' => $hashExisting['id']])->execute();
                } else {
                    // Insert new record
                    $result = Yii::$app->db->createCommand()->insert('{{%job_mapping}}', [
                        'queue_job_id' => $queueJobId,
                        'job_hash' => $jobHash,
                        'export_job_id' => $exportJobId,
                    ])->execute();
                }
            }
            
            $transaction->commit();
            
            if ($result) {
                error_log("Successfully stored mappings: queueJobId=$queueJobId, jobHash=$jobHash, exportJobId=$exportJobId");
            }
            
            return (bool)$result;
        } catch (Exception $e) {
            $transaction->rollBack();
            error_log("Failed to store job mappings: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get export job ID from queue job ID
     * @param string $queueJobId Queue job ID
     * @return string|null Export job ID or null if not found
     */
    public function getExportJobId($queueJobId)
    {
        try {
            $mapping = Yii::$app->db->createCommand("
                SELECT jm.export_job_id 
                FROM {{%job_mapping}} jm 
                WHERE jm.queue_job_id = :queueJobId
                LIMIT 1
            ")->bindValue(':queueJobId', $queueJobId)->queryOne();
            
            return $mapping['export_job_id'] ?? null;
        } catch (Exception $e) {
            error_log("Failed to get export job ID: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get job hash from queue job ID
     * @param string $queueJobId Queue job ID
     * @return string|null Job hash or null if not found
     */
    public function getJobHash($queueJobId)
    {
        try {
            $mapping = Yii::$app->db->createCommand("
                SELECT jm.job_hash 
                FROM {{%job_mapping}} jm 
                WHERE jm.queue_job_id = :queueJobId
                LIMIT 1
            ")->bindValue(':queueJobId', $queueJobId)->queryOne();
            
            return $mapping['job_hash'] ?? null;
        } catch (Exception $e) {
            error_log("Failed to get job hash: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get export job ID directly from job hash
     * @param string $jobHash Job hash
     * @return string|null Export job ID or null if not found
     */
    public function getExportJobIdFromHash($jobHash)
    {
        try {
            $mapping = Yii::$app->db->createCommand("
                SELECT jm.export_job_id 
                FROM {{%job_mapping}} jm 
                WHERE jm.job_hash = :jobHash
                LIMIT 1
            ")->bindValue(':jobHash', $jobHash)->queryOne();
            
            return $mapping['export_job_id'] ?? null;
        } catch (Exception $e) {
            error_log("Failed to get export job ID from hash: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Migrate old JSON mappings to database
     * @return bool Success status
     */
    public function migrateOldMappings()
    {
        $oldMappingFile = Yii::getAlias('@backend/runtime/export-jobs') . '/job_mappings.json';
        
        if (!file_exists($oldMappingFile)) {
            echo "No old mapping file found. Nothing to migrate.\n";
            return true;
        }

        $transaction = Yii::$app->db->beginTransaction();
        
        try {
            // Read old JSON mappings
            $content = file_get_contents($oldMappingFile);
            if ($content === false) {
                throw new \Exception("Failed to read old mapping file: $oldMappingFile");
            }

            $mappings = json_decode($content, true);
            if (!is_array($mappings)) {
                throw new \Exception("Invalid JSON in old mapping file");
            }

            $queueToHash = $mappings['queue_to_hash'] ?? [];
            $hashToExportId = $mappings['hash_to_export_id'] ?? [];
            
            $migratedCount = 0;
            
            // Migrate mappings
            foreach ($queueToHash as $queueJobId => $jobHash) {
                $exportJobId = $hashToExportId[$jobHash] ?? null;
                
                if ($exportJobId) {
                    // Check if already exists
                    $existing = Yii::$app->db->createCommand("
                        SELECT id FROM {{%job_mapping}} WHERE queue_job_id = :queueJobId
                    ")->bindValue(':queueJobId', $queueJobId)->queryOne();
                    
                    if (!$existing) {
                        Yii::$app->db->createCommand()->insert('{{%job_mapping}}', [
                            'queue_job_id' => $queueJobId,
                            'job_hash' => $jobHash,
                            'export_job_id' => $exportJobId,
                        ])->execute();
                        $migratedCount++;
                    }
                }
            }
            
            $transaction->commit();
            
            // Backup old file
            $backupFile = $oldMappingFile . '.backup';
            if (rename($oldMappingFile, $backupFile)) {
                echo "Successfully migrated $migratedCount mappings to database.\n";
                echo "Old file backed up to: $backupFile\n";
            } else {
                echo "Migration completed but failed to backup old file.\n";
            }
            
            return true;
        } catch (\Exception $e) {
            $transaction->rollBack();
            echo "Migration failed: " . $e->getMessage() . "\n";
            return false;
        }
    }

    /**
     * Get statistics about mappings
     * @return array Statistics data
     */
    public function getStatistics()
    {
        try {
            $stats = Yii::$app->db->createCommand("
                SELECT 
                    COUNT(*) as total_mappings,
                    COUNT(DISTINCT queue_job_id) as unique_queue_jobs,
                    COUNT(DISTINCT job_hash) as unique_hashes,
                    COUNT(DISTINCT export_job_id) as unique_exports,
                    MIN(created_at) as oldest_mapping,
                    MAX(created_at) as newest_mapping
                FROM {{%job_mapping}}
            ")->queryOne();
            
            return $stats;
        } catch (Exception $e) {
            error_log("Failed to get statistics: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Clean up old mappings (optional maintenance)
     * @param int $daysOld Remove mappings older than this many days
     * @return int Number of mappings removed
     */
    public function cleanupOldMappings($daysOld = 30)
    {
        try {
            $deleted = Yii::$app->db->createCommand("
                DELETE FROM {{%job_mapping}} 
                WHERE created_at < DATE_SUB(NOW(), INTERVAL :daysOld DAY)
            ")->bindValue(':daysOld', $daysOld)->execute();
            
            return $deleted;
        } catch (Exception $e) {
            error_log("Failed to cleanup old mappings: " . $e->getMessage());
            return 0;
        }
    }
}
