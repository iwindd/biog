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
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->delete('{{%role}}', ['id' => 7]);
    }
}
