<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%user_thaid}}`.
 */
class m260306_082000_create_user_thaid_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%user_thaid}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'pid' => $this->string(13)->notNull()->unique(),
            'created_at' => $this->dateTime()->notNull(),
        ]);

        // creates index for column `user_id`
        $this->createIndex(
            '{{%idx-user_thaid-user_id}}',
            '{{%user_thaid}}',
            'user_id'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-user_thaid-user_id}}',
            '{{%user_thaid}}',
            'user_id',
            '{{%user}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `{{%user}}`
        $this->dropForeignKey(
            '{{%fk-user_thaid-user_id}}',
            '{{%user_thaid}}'
        );

        // drops index for column `user_id`
        $this->dropIndex(
            '{{%idx-user_thaid-user_id}}',
            '{{%user_thaid}}'
        );

        $this->dropTable('{{%user_thaid}}');
    }
}
