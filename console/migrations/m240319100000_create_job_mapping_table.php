<?php

use yii\db\Migration;

/**
 * Create job_mapping table for storing queue job mappings in database
 */
class m240319100000_create_job_mapping_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%job_mapping}}', [
            'id' => $this->primaryKey(),
            'queue_job_id' => $this->string(255)->notNull(),
            'job_hash' => $this->string(255)->notNull(),
            'export_job_id' => $this->string(255)->notNull(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        // Create indexes for performance and constraints
        $this->createIndex('idx-job_mapping-job_hash', '{{%job_mapping}}', 'job_hash');
        $this->createIndex('idx-job_mapping-export_job_id', '{{%job_mapping}}', 'export_job_id');
        
        // Add composite unique constraint to prevent exact duplicates (fixes constraint violation issue)
        $this->createIndex('idx-job_mapping-unique_queue_hash', '{{%job_mapping}}', ['queue_job_id', 'job_hash'], true);
        
        // Add composite index for hash-based lookups (fixes performance issue)
        $this->createIndex('idx-job_mapping-hash_export', '{{%job_mapping}}', ['job_hash', 'export_job_id']);
        
        // Add individual lookup index on queue_job_id
        $this->createIndex('idx-job_mapping-queue_job_id_lookup', '{{%job_mapping}}', 'queue_job_id');
        
        echo "job_mapping table created successfully.\n";
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Drop table (this will automatically drop all indexes)
        $this->dropTable('{{%job_mapping}}');
        echo "job_mapping table dropped.\n";
    }
}
