<?php

use yii\db\Migration;

/**
 * Class m270227_053000_add_label_to_file_center_table
 */
class m270227_053000_add_label_to_file_center_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%file_center}}', 'label', $this->string(255)->unique()->after('file_name'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%file_center}}', 'label');
    }
}
