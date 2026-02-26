<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%content_data_source}}`.
 */
class m260226_113000_create_content_data_source_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%content_data_source}}', [
            'id' => $this->primaryKey(),
            'content_id' => $this->integer()->notNull(),
            'source_name' => $this->string(255),
            'author' => $this->string(255),
            'published_date' => $this->date(),
            'reference_url' => $this->string(255)->notNull(),
        ]);

        // creates index for column `content_id`
        $this->createIndex(
            '{{%idx-content_data_source-content_id}}',
            '{{%content_data_source}}',
            'content_id'
        );

        // add foreign key for table `{{%content}}`
        $this->addForeignKey(
            '{{%fk-content_data_source-content_id}}',
            '{{%content_data_source}}',
            'content_id',
            '{{%content}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `{{%content}}`
        $this->dropForeignKey(
            '{{%fk-content_data_source-content_id}}',
            '{{%content_data_source}}'
        );

        // drops index for column `content_id`
        $this->dropIndex(
            '{{%idx-content_data_source-content_id}}',
            '{{%content_data_source}}'
        );

        $this->dropTable('{{%content_data_source}}');
    }
}
