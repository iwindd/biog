<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%content_type}}`.
 */
class m260224_073800_create_content_type_table extends Migration
{
  /**
   * {@inheritdoc}
   */
  public function safeUp()
  {
    $this->createTable('{{%content_type}}', [
      'id' => $this->primaryKey(),
      'name' => $this->string()->notNull()->unique(),
      'title' => $this->string()->notNull(),
      'is_visible' => $this->boolean()->defaultValue(1),
      'created_at' => $this->dateTime()->notNull(),
      'updated_at' => $this->dateTime()->notNull(),
    ]);

    // Insert default content types
    $now = date('Y-m-d H:i:s');
    $this->batchInsert('{{%content_type}}', ['name', 'title', 'is_visible', 'created_at', 'updated_at'], [
      ['plant', 'พรรณไม้', 1, $now, $now],
      ['animal', 'สัตว์', 1, $now, $now],
      ['fungi', 'เห็ดรา', 1, $now, $now],
      ['expert', 'ปราชญ์', 1, $now, $now],
      ['ecotourism', 'ท่องเที่ยวเชิงนิเวศ', 1, $now, $now],
      ['product', 'ผลิตภัณฑ์', 1, $now, $now],
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function safeDown()
  {
    $this->dropTable('{{%content_type}}');
  }
}
