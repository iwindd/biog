<?php

use yii\db\Migration;

/**
 * Create index idx_content_filter on content table.
 */
class m260302_042500_create_content_filter_index extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createIndex(
            'idx_content_filter',
            'content',
            ['active', 'type_id', 'status', 'is_hidden', 'updated_at']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx_content_filter', 'content');
    }
}
