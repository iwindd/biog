<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%file_center}}`.
 */
class m260225_030000_create_file_center_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%file_center}}', [
            'id' => $this->primaryKey(),
            'file_name' => $this->string(255)->notNull(),
            'file_path' => $this->string(255)->notNull(),
            'file_type' => $this->string(100)->notNull(),
            'file_size' => $this->integer()->notNull(),
            'alt_text' => $this->string(255),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
            'created_by' => $this->integer(),
        ], $tableOptions);
        
        $this->createIndex(
            '{{%idx-file_center-file_type}}',
            '{{%file_center}}',
            'file_type'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex(
            '{{%idx-file_center-file_type}}',
            '{{%file_center}}'
        );
        $this->dropTable('{{%file_center}}');
    }
}
