<?php

namespace common\components;

use Yii;
use yii\base\Component;

/**
 * JobMappingService handles all export job mapping operations with atomic file locking
 * to prevent race conditions and ensure data consistency.
 */
class JobMappingService extends Component
{
    private $mappingFile;
    private $lockTimeout = 5; // seconds
    private $retryInterval = 100; // milliseconds

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->mappingFile = Yii::getAlias('@backend/runtime/export-jobs') . '/job_mappings.json';
        $this->ensureMappingFile();
    }

    /**
     * Atomically store all mappings for a job
     * @param string $queueJobId Queue job ID
     * @param string $jobHash Job hash
     * @param string $exportJobId Export job ID
     * @return bool Success status
     */
    public function storeMappings($queueJobId, $jobHash, $exportJobId)
    {
        $handle = $this->acquireExclusiveLock();
        if (!$handle) {
            error_log("Failed to acquire exclusive lock for job mapping storage");
            return false;
        }

        try {
            $mappings = $this->readMappingsFromFile();
            
            // Update mappings atomically
            $mappings['queue_to_hash'][$queueJobId] = $jobHash;
            $mappings['hash_to_export_id'][$jobHash] = $exportJobId;
            
            $result = $this->writeMappingsToFile($mappings, $handle);
            
            if ($result) {
                error_log("Successfully stored mappings: queueJobId=$queueJobId, jobHash=$jobHash, exportJobId=$exportJobId");
            }
            
            return $result;
        } finally {
            $this->releaseLock($handle);
        }
    }

    /**
     * Get export job ID from queue job ID
     * @param string $queueJobId Queue job ID
     * @return string|null Export job ID or null if not found
     */
    public function getExportJobId($queueJobId)
    {
        $handle = $this->acquireSharedLock();
        if (!$handle) {
            error_log("Failed to acquire shared lock for reading export job ID");
            return null;
        }

        try {
            $mappings = $this->readMappingsFromFile();
            $jobHash = $mappings['queue_to_hash'][$queueJobId] ?? null;
            
            if ($jobHash) {
                return $mappings['hash_to_export_id'][$jobHash] ?? null;
            }
            
            return null;
        } finally {
            $this->releaseLock($handle);
        }
    }

    /**
     * Get job hash from queue job ID
     * @param string $queueJobId Queue job ID
     * @return string|null Job hash or null if not found
     */
    public function getJobHash($queueJobId)
    {
        $handle = $this->acquireSharedLock();
        if (!$handle) {
            error_log("Failed to acquire shared lock for reading job hash");
            return null;
        }

        try {
            $mappings = $this->readMappingsFromFile();
            return $mappings['queue_to_hash'][$queueJobId] ?? null;
        } finally {
            $this->releaseLock($handle);
        }
    }

    /**
     * Get export job ID directly from job hash
     * @param string $jobHash Job hash
     * @return string|null Export job ID or null if not found
     */
    public function getExportJobIdFromHash($jobHash)
    {
        $handle = $this->acquireSharedLock();
        if (!$handle) {
            error_log("Failed to acquire shared lock for reading export job ID from hash");
            return null;
        }

        try {
            $mappings = $this->readMappingsFromFile();
            return $mappings['hash_to_export_id'][$jobHash] ?? null;
        } finally {
            $this->releaseLock($handle);
        }
    }

    /**
     * Ensure the mapping file exists with proper structure
     */
    private function ensureMappingFile()
    {
        $mappingDir = dirname($this->mappingFile);
        if (!is_dir($mappingDir)) {
            if (!mkdir($mappingDir, 0755, true)) {
                throw new \RuntimeException("Failed to create mapping directory: $mappingDir");
            }
        }

        if (!file_exists($this->mappingFile)) {
            $defaultStructure = [
                'queue_to_hash' => [],
                'hash_to_export_id' => []
            ];
            
            if (file_put_contents($this->mappingFile, json_encode($defaultStructure, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)) === false) {
                throw new \RuntimeException("Failed to create mapping file: {$this->mappingFile}");
            }
        }
    }

    /**
     * Read mappings from file with error handling
     * @return array Mappings data
     */
    private function readMappingsFromFile()
    {
        if (!file_exists($this->mappingFile)) {
            return ['queue_to_hash' => [], 'hash_to_export_id' => []];
        }

        $content = file_get_contents($this->mappingFile);
        if ($content === false) {
            error_log("Failed to read mapping file: {$this->mappingFile}");
            return ['queue_to_hash' => [], 'hash_to_export_id' => []];
        }

        $mappings = json_decode($content, true);
        if (!is_array($mappings)) {
            error_log("Invalid JSON in mapping file, using default structure");
            return ['queue_to_hash' => [], 'hash_to_export_id' => []];
        }

        // Ensure structure integrity
        $mappings['queue_to_hash'] = $mappings['queue_to_hash'] ?? [];
        $mappings['hash_to_export_id'] = $mappings['hash_to_export_id'] ?? [];

        return $mappings;
    }

    /**
     * Write mappings to file with error handling
     * @param array $mappings Mappings data
     * @param resource $handle File handle with lock
     * @return bool Success status
     */
    private function writeMappingsToFile($mappings, $handle)
    {
        // Truncate file before writing
        ftruncate($handle, 0);
        rewind($handle);

        $jsonContent = json_encode($mappings, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        if ($jsonContent === false) {
            error_log("Failed to encode mappings to JSON");
            return false;
        }

        $bytesWritten = fwrite($handle, $jsonContent);
        if ($bytesWritten === false) {
            error_log("Failed to write mappings to file");
            return false;
        }

        // Ensure data is written to disk
        fflush($handle);
        
        return true;
    }

    /**
     * Acquire exclusive lock for writing
     * @return resource|null File handle or null if failed
     */
    private function acquireExclusiveLock()
    {
        return $this->acquireLock(LOCK_EX);
    }

    /**
     * Acquire shared lock for reading
     * @return resource|null File handle or null if failed
     */
    private function acquireSharedLock()
    {
        return $this->acquireLock(LOCK_SH);
    }

    /**
     * Acquire file lock with retry logic
     * @param int $lockType LOCK_SH for shared, LOCK_EX for exclusive
     * @return resource|null File handle or null if failed
     */
    private function acquireLock($lockType)
    {
        $handle = fopen($this->mappingFile, 'c+');
        if (!$handle) {
            error_log("Failed to open mapping file for locking: {$this->mappingFile}");
            return null;
        }

        $startTime = time();
        while (time() - $startTime < $this->lockTimeout) {
            if (flock($handle, $lockType | LOCK_NB)) {
                return $handle;
            }
            usleep($this->retryInterval * 1000); // Convert to microseconds
        }

        error_log("Failed to acquire lock within timeout: {$this->lockTimeout}s");
        fclose($handle);
        return null;
    }

    /**
     * Release file lock
     * @param resource $handle File handle
     */
    private function releaseLock($handle)
    {
        if ($handle) {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }
}
