<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%content_image_source}}`.
 */
class m260226_100000_create_content_image_source_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%content_image_source}}', [
            'id' => $this->primaryKey(),
            'content_id' => $this->integer()->notNull(),
            'source_name' => $this->string(255)->null(),
            'author' => $this->string(255)->null(),
            'published_date' => $this->date()->null(),
            'reference_url' => $this->string(255)->null(),
        ]);

        // creates index for column `content_id`
        $this->createIndex(
            '{{%idx-content_image_source-content_id}}',
            '{{%content_image_source}}',
            'content_id'
        );

        // add foreign key for table `{{%content}}`
        $this->addForeignKey(
            '{{%fk-content_image_source-content_id}}',
            '{{%content_image_source}}',
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
            '{{%fk-content_image_source-content_id}}',
            '{{%content_image_source}}'
        );

        // drops index for column `content_id`
        $this->dropIndex(
            '{{%idx-content_image_source-content_id}}',
            '{{%content_image_source}}'
        );

        $this->dropTable('{{%content_image_source}}');
    }
}
