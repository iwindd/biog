<?php

use yii\db\Migration;

/**
 * Handles adding "approved_student" variable to the `variables` table.
 */
class m260304_120000_add_approved_student_variable extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->insert('{{%variables}}', [
            'key' => 'approved_student',
            'value' => 'สวัสดี [student-name],' . "\r\n\r\n" . 'เจ้าหน้าที่ได้ทำการเปลี่ยนสถานะของท่านเป็น [status] ' . "\r\n\r\n" . 'เข้าสู่ระบบได้ที่ลิงก์ [link]' . "\r\n\r\n" . 'Warm regards, ' . "\r\n" . 'BIOGANG.',
            'description' => 'เปลี่ยนแปลงสถานะนักเรียน',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->delete('{{%variables}}', ['key' => 'approved_student']);
    }
}
