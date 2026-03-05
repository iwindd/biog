<?php

use yii\db\Migration;

/**
 * Add indexes for search optimization on content table.
 * - Composite index on (active, status, updated_at) for filtered listing queries
 * - FULLTEXT index on (name) for keyword search
 */
class m260305_053300_add_content_search_indexes extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createIndex(
            'idx_content_active_status',
            'content',
            ['active', 'status', 'updated_at']
        );

        $this->execute('ALTER TABLE `content` ADD FULLTEXT INDEX `idx_content_name_ft` (`name`)');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx_content_active_status', 'content');
        $this->dropIndex('idx_content_name_ft', 'content');
    }
}
