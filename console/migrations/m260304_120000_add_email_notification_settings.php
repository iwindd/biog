<?php

use yii\db\Migration;

/**
 * Handles creating `user_notification_settings` table and email template variable.
 */
class m260304_120000_add_email_notification_settings extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%user_notification_settings}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull()->unique(),
            'notify_new_registration' => $this->tinyInteger()->notNull()->defaultValue(0),
            'created_at' => $this->dateTime()->defaultExpression('NOW()'),
            'updated_at' => $this->dateTime()->defaultExpression('NOW()'),
        ]);

        $this->addForeignKey(
            'fk_user_notification_settings_user',
            '{{%user_notification_settings}}',
            'user_id',
            '{{%user}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        // Email template for admin notification
        $this->insert('{{%variables}}', [
            'key' => 'new_registration_notification',
            'value' => "เรียน Admin,\n\nมีการสมัครสมาชิกใหม่ที่ต้องการการอนุมัติ:\n\nชื่อ-นามสกุล: [user-name]\nอีเมล: [user-email]\nประเภท: [user-role]\n\nกรุณาตรวจสอบและอนุมัติได้ที่: [link]\n\nขอบคุณครับ\nทีมงาน BIOGANG",
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_user_notification_settings_user', '{{%user_notification_settings}}');
        $this->dropTable('{{%user_notification_settings}}');
        $this->delete('{{%variables}}', ['key' => 'new_registration_notification']);
    }
}
