<?php

use yii\db\Migration;

/**
 * Handles adding "Pending Student" role to the `role` table.
 */
class m260304_042500_add_pending_student_role extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->insert('{{%role}}', [
            'id' => 7,
            'name' => 'Pending Student',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $this->insert('{{%role_permission}}', [
            'role_id' => 7,
            'permission_id' => 36,
        ]);

        $this->insert('{{%role_permission}}', [
            'role_id' => 7,
            'permission_id' => 101,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->delete('{{%role_permission}}', ['role_id' => 7, 'permission_id' => 36]);
        $this->delete('{{%role_permission}}', ['role_id' => 7, 'permission_id' => 101]);
        $this->delete('{{%role}}', ['id' => 7]);
    }
}
